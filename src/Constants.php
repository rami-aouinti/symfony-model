<?php

declare(strict_types=1);

namespace App;

/**
 * @package App
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class Constants
{
    /**
     * The current release version
     */
    public const string VERSION = '1.0.0';
    /**
     * The current release: major * 10000 + minor * 100 + patch
     */
    public const int VERSION_ID = 100;
    /**
     * The software name
     */
    public const string SOFTWARE = 'Platform';
    /**
     * Used in multiple views
     */
    public const string GITHUB = 'https://github.com/rami-aouinti/symfony-model/';
    /**
     * The GitHub repository name
     */
    public const string GITHUB_REPO = 'rami-aouinti/symfony-model';
    /**
     * Homepage, used in multiple views
     */
    public const string HOMEPAGE = 'https://broworld.de/';
    /**
     * Default color for Customer, Project and Activity entities
     */
    public const string DEFAULT_COLOR = '#d2d6de';
}
