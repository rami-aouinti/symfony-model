<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Application\Config;

use App\Platform\Application\Utils\GPSConverter;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.1 (2022-07-16)
 * @since 1.0.1 (2022-07-16) Fix empty current request for cli commands.
 * @since 1.0.0 (2022-07-03) First version.
 * @package App\Config
 */
class SearchConfig
{
    final public const string ORDER_BY_LOCATION = 'l';
    final public const string ORDER_BY_NAME = 'n';
    final public const string ORDER_BY_RELEVANCE = 'r';
    final public const string ORDER_BY_RELEVANCE_LOCATION = 'rl';

    final public const int VIEW_MODE_SEARCH = 0;
    final public const int VIEW_MODE_LIST = 1;
    final public const int VIEW_MODE_DETAIL = 2;
    final public const int VIEW_MODE_CURRENT_POSITION = 3;

    final public const string PARAMETER_NAME_ERROR = 'e';
    final public const null PARAMETER_DEFAULT_ERROR = null;

    final public const string PARAMETER_NAME_ID_STRING = 'id';
    final public const null PARAMETER_DEFAULT_ID_STRING = null;

    final public const string PARAMETER_NAME_LOCATION = 'l';
    final public const null PARAMETER_DEFAULT_LOCATION = null;

    final public const string PARAMETER_NAME_NUMBER_PER_PAGE = 'n';
    final public const int PARAMETER_DEFAULT_NUMBER_PER_PAGE = 10;

    final public const string PARAMETER_NAME_NUMBER_RESULTS = 'r';
    final public const int PARAMETER_DEFAULT_NUMBER_RESULTS = 0;

    final public const string PARAMETER_NAME_PAGE = 'p';
    final public const int PARAMETER_DEFAULT_PAGE = 1;

    final public const string PARAMETER_NAME_SEARCH_QUERY = 'q';
    final public const null PARAMETER_DEFAULT_SEARCH_QUERY = null;

    final public const string PARAMETER_NAME_SORT = 's';
    final public const string PARAMETER_DEFAULT_SORT = self::ORDER_BY_RELEVANCE;

    final public const string PARAMETER_NAME_VERBOSE = 'v';
    final public const bool PARAMETER_DEFAULT_VERBOSE = false;
    protected ?Request $request;

    protected ?string $error = null;

    /* The id string like a:189454, etc. */
    protected ?string $idString = null;

    /* The current location like 51.061182,13.740584, etc. */
    /**
     * @var float[]|null
     */
    protected ?array $location;

    /* The number of search items per page. */
    protected int $numberPerPage;

    /* The number of results. */
    protected int $numberResults;

    /* The current visible page like 1, etc. */
    protected int $page;

    /* The search query like "Dresden", etc. */
    protected ?string $searchQuery = null;

    /* The sort order of search list. */
    protected string $sort;

    /* Verbose mode */
    protected bool $verbose = false;

    /**
     * @throws Exception
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        /* Request all parameters. */
        $this->requestParameters();
    }

    /**
     * Returns all parameters as array.
     *
     * @return int[]|string[]|bool[]|null[]
     */
    #[ArrayShape([
        self::PARAMETER_NAME_ERROR => 'null|string',
        self::PARAMETER_NAME_ID_STRING => 'null|string',
        self::PARAMETER_NAME_LOCATION => 'null|string',
        self::PARAMETER_NAME_NUMBER_PER_PAGE => 'int',
        self::PARAMETER_NAME_PAGE => 'int',
        self::PARAMETER_NAME_SEARCH_QUERY => 'null|string',
        self::PARAMETER_NAME_SORT => 'string',
        self::PARAMETER_NAME_VERBOSE => 'bool',
    ])]
    public function getParameterArray(): array
    {
        return [
            self::PARAMETER_NAME_ERROR => $this->getError(),
            self::PARAMETER_NAME_ID_STRING => $this->getIdString(),
            self::PARAMETER_NAME_LOCATION => $this->getLocationString(),
            self::PARAMETER_NAME_NUMBER_PER_PAGE => $this->getNumberPerPage(),
            self::PARAMETER_NAME_PAGE => $this->getPage(),
            self::PARAMETER_NAME_SEARCH_QUERY => $this->getSearchQuery(),
            self::PARAMETER_NAME_SORT => $this->getSort(),
            self::PARAMETER_NAME_VERBOSE => $this->isVerbose(),
        ];
    }

    /**
     * Gets error of this search request.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Sets error of this search request.
     *
     * @return $this
     */
    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Returns the id string.
     */
    public function getIdString(): ?string
    {
        return $this->idString;
    }

    /**
     * Returns the id string.
     */
    public function hasIdString(): bool
    {
        return $this->idString !== null;
    }

    /**
     * Returns the id string.
     *
     * @return $this
     */
    public function setIdString(?string $idString): self
    {
        $this->idString = $idString;

        return $this;
    }

    /**
     * Returns the current location of user.
     *
     * @return float[]|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * Returns if the current location of user is available.
     */
    public function hasLocation(): bool
    {
        return $this->location !== null;
    }

    /**
     * Sets the current location of user.
     *
     * @param float[]|null $location
     * @return $this
     */
    public function setLocation(?array $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Returns the current location of user.
     */
    public function getLocationString(): ?string
    {
        if ($this->location === null) {
            return null;
        }

        return implode(',', $this->location);
    }

    /**
     * Sets the current location of user.
     *
     * @return $this
     * @throws Exception
     */
    public function setLocationString(?string $location): self
    {
        if ($location === null) {
            $this->location = null;

            return $this;
        }

        $parsedLocation = GPSConverter::parseFullLocation2DecimalDegrees($location);

        if ($parsedLocation === false) {
            throw new Exception(sprintf('Unable to parse location "%s" (%s:%d).', $location, __FILE__, __LINE__));
        }

        $this->location = $parsedLocation;

        return $this;
    }

    /**
     * Gets the number of search items per page.
     */
    public function getNumberPerPage(): int
    {
        return $this->numberPerPage;
    }

    /**
     * Sets the number of search items per page.
     *
     * @return $this
     */
    public function setNumberPerPage(int $numberPerPage): self
    {
        $this->numberPerPage = $numberPerPage;

        return $this;
    }

    /**
     * Returns the number of results.
     */
    public function getNumberResults(): int
    {
        return $this->numberResults;
    }

    /**
     * Sets the number of results.
     *
     * @return $this
     */
    public function setNumberResults(int $numberResults): self
    {
        $this->numberResults = $numberResults;

        return $this;
    }

    /**
     * Returns the given page.
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returns the given page.
     *
     * @return $this
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Returns the search query.
     */
    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    /**
     * Returns if search query is available.
     */
    public function hasSearchQuery(): bool
    {
        return $this->searchQuery !== null;
    }

    /**
     * Sets the search query.
     *
     * @return $this
     * @throws Exception
     */
    public function setSearchQuery(?string $searchQuery): self
    {
        $this->searchQuery = null;

        if ($searchQuery === null) {
            return $this;
        }

        $searchQuery = trim($searchQuery);

        /* ID string was found. */
        if (preg_match('~^[ahlprstuv]:\d+$~', $searchQuery)) {
            $this->setIdString($searchQuery);

            return $this;
        }

        $locationParsed = GPSConverter::parseFullLocation2DecimalDegrees($searchQuery);

        if ($locationParsed !== false) {
            $this->setLocation($locationParsed);

            return $this;
        }

        $this->searchQuery = $searchQuery;

        return $this;
    }

    /**
     * Returns the sort mode.
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * Sets the sort mode.
     *
     * Possible sort modes:
     *
     * r - relevance
     * n - name
     * l - location (needs $location !== 0)
     * rl - relevance and location (needs $location !== 0)
     *
     * @return $this
     * @throws Exception
     */
    public function setSort(string $sort): self
    {
        if (!in_array($sort, [self::ORDER_BY_LOCATION, self::ORDER_BY_NAME, self::ORDER_BY_RELEVANCE, self::ORDER_BY_RELEVANCE_LOCATION])) {
            throw new Exception(sprintf('Unsupported sort mode "%s" (%s:%d).', $sort, __FILE__, __LINE__));
        }

        $this->sort = $sort;

        return $this;
    }

    /**
     * Gets the verbose mode.
     */
    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    /**
     * Sets the verbose parameter.
     *
     * @return $this
     */
    public function setVerbose(bool $verbose): self
    {
        $this->verbose = $verbose;

        return $this;
    }

    /**
     * Returns the current request.
     *
     * @throws Exception
     */
    public function getRequest(): Request
    {
        if ($this->request === null) {
            throw new Exception(sprintf('Unable to get current request (%s:%d).', __FILE__, __LINE__));
        }

        return $this->request;
    }

    /**
     * Returns the mode of this search:
     *
     * 0 - No search, empty search, error
     * 1 - list
     * 2 - detail
     * 3 - current location search
     */
    public function getViewMode(): int
    {
        if ($this->getError() !== null) {
            return self::VIEW_MODE_SEARCH; // 0
        }

        return match (true) {
            $this->getIdString() !== null => self::VIEW_MODE_DETAIL, // 2
            $this->getSearchQuery() !== null => self::VIEW_MODE_LIST, // 1
            $this->getLocation() !== null => self::VIEW_MODE_CURRENT_POSITION, // 3
            default => self::VIEW_MODE_SEARCH, // 0
        };
    }

    /**
     * Returns all needed search input form elements.
     */
    public function getInputs(): string
    {
        $search = match (true) {
            $this->getSearchQuery() !== null => $this->getSearchQuery(),
            $this->getIdString() !== null => $this->getIdString(),
            $this->getLocationString() !== null => $this->getLocationString(),
            default => '',
        };

        $inputs = sprintf(
            '<input type="search" id="%s" name="%s" required="required" autofocus="autofocus" value="%s">',
            self::PARAMETER_NAME_SEARCH_QUERY,
            self::PARAMETER_NAME_SEARCH_QUERY,
            $search
        );

        $inputs .= sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s">',
            self::PARAMETER_NAME_SORT,
            self::PARAMETER_NAME_SORT,
            $this->getSort()
        );

        $inputs .= sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s">',
            self::PARAMETER_NAME_PAGE,
            self::PARAMETER_NAME_PAGE,
            $this->getPage()
        );

        if ($this->getLocationString() !== null) {
            $inputs .= sprintf(
                '<input type="hidden" id="%s" name="%s" value="%s">',
                self::PARAMETER_NAME_LOCATION,
                self::PARAMETER_NAME_LOCATION,
                $this->getLocationString()
            );
        }

        if ($this->isVerbose()) {
            $inputs .= sprintf(
                '<input type="hidden" id="%s" name="%s" value="1">',
                self::PARAMETER_NAME_VERBOSE,
                self::PARAMETER_NAME_VERBOSE
            );
        }

        return $inputs;
    }

    /**
     * Returns the next page.
     */
    public function getNextPage(): ?int
    {
        $nextPage = null;

        if ($this->getViewMode() !== self::VIEW_MODE_LIST) {
            return null;
        }

        $numberLastElement = min($this->getPage() * $this->getNumberPerPage(), $this->getNumberResults());

        if ($this->getNumberResults() > $numberLastElement) {
            $nextPage = $this->getPage() + 1;
        }

        return $nextPage;
    }

    /**
     * Requests all necessary parameters.
     *
     * @throws Exception
     */
    protected function requestParameters(): void
    {
        if ($this->request === null) {
            return;
        }

        $this->setError(null);
        $this->setIdString(
            $this->request->query->has(self::PARAMETER_NAME_ID_STRING) ?
                strval($this->request->query->get(self::PARAMETER_NAME_ID_STRING)) :
                self::PARAMETER_DEFAULT_ID_STRING
        );
        $this->setLocationString(
            $this->request->query->has(self::PARAMETER_NAME_LOCATION) ?
                strval($this->request->query->get(self::PARAMETER_NAME_LOCATION)) :
                self::PARAMETER_DEFAULT_LOCATION
        );
        $this->setNumberPerPage(
            $this->request->query->has(self::PARAMETER_NAME_NUMBER_PER_PAGE) ?
                intval($this->request->query->get(self::PARAMETER_NAME_NUMBER_PER_PAGE)) :
                self::PARAMETER_DEFAULT_NUMBER_PER_PAGE
        );
        $this->setNumberResults(self::PARAMETER_DEFAULT_NUMBER_RESULTS);
        $this->setPage(
            $this->request->query->has(self::PARAMETER_NAME_PAGE) ?
                intval($this->request->query->get(self::PARAMETER_NAME_PAGE)) :
                self::PARAMETER_DEFAULT_PAGE
        );
        $this->setSearchQuery(
            $this->request->query->has(self::PARAMETER_NAME_SEARCH_QUERY) ?
                strval($this->request->query->get(self::PARAMETER_NAME_SEARCH_QUERY)) :
                self::PARAMETER_DEFAULT_SEARCH_QUERY
        );
        $this->setSort(
            $this->request->query->has(self::PARAMETER_NAME_SORT) ?
                strval($this->request->query->get(self::PARAMETER_NAME_SORT)) :
                self::PARAMETER_DEFAULT_SORT
        );
        $this->setVerbose(
            $this->request->query->has(self::PARAMETER_NAME_VERBOSE) ?
                boolval($this->request->query->get(self::PARAMETER_NAME_VERBOSE)) :
                self::PARAMETER_DEFAULT_VERBOSE
        );
    }
}
