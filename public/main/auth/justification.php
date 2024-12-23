<?php

/* For licensing terms, see /license.txt */

use App\CoreBundle\Entity\PersonalFile;
use App\CoreBundle\Framework\Container;

$cidReset = true;
require_once __DIR__.'/../inc/global.inc.php';

api_block_anonymous_users(true);

$allowJustification = 'true' === api_get_plugin_setting('justification', 'tool_enable');

if (!$allowJustification) {
    api_not_allowed(true);
}
$personalRepo = Container::getPersonalFileRepository();
$userId = api_get_user_id();
$user = api_get_user_entity();

$justification = '';
$plugin = Justification::create();
$fields = $plugin->getList();
$formValidator = new FormValidator('justification');
$formValidator->addHeader($plugin->get_lang('Justification'));
foreach ($fields as $field) {
    $formValidator->addHtml('<a name="'.$field['code'].'"></a>');
    $formValidator->addFile($field['code'].'_file', [$field['name'], $field['comment']]);
    if ($field['date_manual_on']) {
        $formValidator->addDatePicker($field['code'].'_date', $plugin->get_lang('DateValidity'));
    }
    $formValidator->addHtml('<hr>');
}

$formValidator->addButtonSend(get_lang('Send'));
if ($formValidator->validate() && isset($_FILES)) {
    foreach ($fields as $field) {
        $fieldId = $field['id'];

        $days = $field['validity_duration'];
        $fileNameKey = '';
        if (isset($_FILES[$field['code'].'_file']) && !empty($_FILES[$field['code'].'_file']['tmp_name'])) {
            $file = $_FILES[$field['code'].'_file'];
            $fileNameKey = $field['code'].'_file';
        } else {
            continue;
        }

        $date = isset($_REQUEST[$field['code'].'_date']) ? $_REQUEST[$field['code'].'_date'].' 13:00:00' : api_get_local_time();

        $startDate = api_get_utc_datetime($date, false, true);

        $interval = new \DateInterval('P'.$days.'D');
        $startDate->add($interval);
        $finalDate = $startDate->format('Y-m-d');

        $file['name'] = api_replace_dangerous_char($file['name']);
        $fileName = $file['name'];

        $params = [
            'file_path' => $fileName,
            'user_id' => api_get_user_id(),
            'date_validity' => $finalDate,
            'justification_document_id' => $fieldId,
        ];
        $id = Database::insert('justification_document_rel_users', $params);

        if ($id) {
            $personalFile = new PersonalFile();
            $personalFile->setTitle($fileName);
            $personalRepo->addFileFromFileRequest($personalFile, $fileNameKey, true);
            Display::addFlash(Display::return_message($plugin->get_lang('JustificationSaved')));
        }
    }

    header('Location: '.api_get_self());
    exit;
}

$userJustifications = $plugin->getUserJustificationList(api_get_user_id());
$userJustificationList = '';
$action = $_REQUEST['a'] ?? '';

$justificationContent = '';
switch ($action) {
    case 'edit_justification':
        $justificationId = isset($_REQUEST['justification_id']) ? (int) $_REQUEST['justification_id'] : '';
        $userJustification = $plugin->getUserJustification($justificationId);
        $justification = $plugin->getJustification($userJustification['justification_document_id']);
        if (0 == $justification['date_manual_on']) {
            api_not_allowed(true);
        }
        $formEdit = new FormValidator(
            'edit',
            'post',
            api_get_self().'?a=edit_justification&justification_id='.$justificationId
        );
        $formEdit->addHeader($justification['name']);
        $element = $formEdit->addDatePicker('date_validity', $plugin->get_lang('ValidityDate'));
        $element->setValue($userJustification['date_validity']);
        $formEdit->addButtonUpdate(get_lang('Update'));
        $formEdit->setDefaults($userJustification);
        $justificationContent = $formEdit->returnForm();
        if ($formEdit->validate()) {
            $values = $formEdit->getSubmitValues();
            $date = Database::escape_string($values['date_validity']);
            $sql = "UPDATE justification_document_rel_users SET date_validity = '$date'
                    WHERE id = $justificationId AND user_id = ".$userId;
            Database::query($sql);
            Display::addFlash(Display::return_message(get_lang('Updated')));
            header('Location: '.api_get_self());
            exit;
        }
        break;
    case 'delete_justification':
        $justificationId = isset($_REQUEST['justification_id']) ? (int) $_REQUEST['justification_id'] : '';
        $userJustification = $plugin->getUserJustification($justificationId);
        if ($userJustification && $userJustification['user_id'] == api_get_user_id()) {
            $personalFile = $personalRepo->getResourceByCreatorFromTitle(
                $userJustification['file_path'],
                $user,
                $user->getResourceNode()
            );
            if (null !== $personalFile) {
                $personalRepo->delete($personalFile);
            }

            $sql = "DELETE FROM justification_document_rel_users
                    WHERE id = $justificationId AND user_id = ".$userId;
            Database::query($sql);
            Display::addFlash(Display::return_message(get_lang('Deleted')));
        }

        header('Location: '.api_get_self());
        exit;
        break;
}

if (!empty($userJustifications)) {
    $userJustificationList .= Display::page_subheader3($plugin->get_lang('MyJustifications'));
    $table = new HTML_Table(['class' => 'data_table']);
    $column = 0;
    $row = 0;
    $headers = [
        get_lang('Name'),
        get_lang('File'),
        $plugin->get_lang('ValidityDate'),
        get_lang('Actions'),
    ];
    foreach ($headers as $header) {
        $table->setHeaderContents($row, $column, $header);
        $column++;
    }
    $row = 1;
    foreach ($userJustifications as $userJustification) {
        $justification = $plugin->getJustification($userJustification['justification_document_id']);

        $personalFile = $personalRepo->findResourceByTitle(
            $userJustification['file_path'],
            $user->getResourceNode()
        );

        $url = $personalRepo->getResourceFileUrl($personalFile);
        //$url = api_get_uploaded_web_url('justification', $userJustification['id'], $userJustification['file_path']);
        $link = Display::url($userJustification['file_path'], $url);
        $col = 0;
        $table->setCellContents($row, $col++, $justification['name']);
        $table->setCellContents($row, $col++, $link);
        $date = $userJustification['date_validity'];
        if ($userJustification['date_validity'] < api_get_local_time()) {
            $date = Display::label($userJustification['date_validity'], 'warning');
        }
        $table->setCellContents($row, $col++, $date);
        $actions = '';

        if (1 == $justification['date_manual_on']) {
            $actions .= Display::url(
                get_lang('Edit'),
                api_get_self().'?a=edit_justification&justification_id='.$userJustification['id'],
                ['class' => 'btn btn--primary']
            );
        }
        $actions .= '&nbsp;'.Display::url(
                get_lang('Delete'),
                api_get_self().'?a=delete_justification&justification_id='.$userJustification['id'],
                ['class' => 'btn btn--danger']
            );
        $table->setCellContents($row, $col++, $actions);
        $row++;
    }

    $userJustificationList .= $justificationContent.$table->toHtml();
}

$tabs = SocialManager::getHomeProfileTabs('justification');
$justification = $tabs.$formValidator->returnForm().$userJustificationList;

$tpl = new Template(get_lang('ModifyProfile'));

SocialManager::setSocialUserBlock($tpl, api_get_user_id(), 'home');
$tpl->assign('social_menu_block', '');
$tpl->assign('social_right_content', $justification);
$social_layout = $tpl->get_template('social/edit_profile.tpl');

$tpl->display($social_layout);
