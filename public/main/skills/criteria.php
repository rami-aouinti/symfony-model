<?php

/* For licensing terms, see /license.txt */

/**
 * Show information about OpenBadge criteria.
 *
 * @author Angel Fernando Quiroz Campos <angel.quiroz@beeznest.com>
 */
require_once __DIR__.'/../inc/global.inc.php';

$skillId = isset($_GET['id']) ? $_GET['id'] : 0;

if (empty($skillId)) {
    exit;
}

$entityManager = Database::getManager();
/** @var \App\CoreBundle\Entity\Skill\Skill $skill */
$skill = $entityManager->find(\App\CoreBundle\Entity\Skill\Skill::class, $_GET['id']);

if ($skill) {
    $skillInfo = [
        'name' => $skill->getTitle(),
        'short_code' => $skill->getShortCode(),
        'description' => $skill->getDescription(),
        'criteria' => $skill->getCriteria(),
        'badge_image' => SkillModel::getWebIconPath($skill),
    ];

    $template = new Template();
    $template->assign('skill_info', $skillInfo);

    $content = $template->fetch(
        $template->get_template('skill/criteria.tpl')
    );

    $template->assign('content', $content);
    $template->display_one_col_template();
    exit;
}

Display::addFlash(
    Display::return_message(get_lang('Skill not found'), 'error')
);

header('Location: '.api_get_path(WEB_PATH));
exit;
