<?php
require_once(dirname(__FILE__) . '/class.srCertificateTypeTemplateFormGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypeFormGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypeTableGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypeSettingsTableGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypePlaceholdersTableGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypeStandardPlaceholdersTableGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypeSettingFormGUI.php');
require_once(dirname(__FILE__) . '/class.srCertificateTypePlaceholderFormGUI.php');

/**
 * GUI-Class srCertificateTypeGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 * @ilCtrl_isCalledBy srCertificateTypeGUI: ilRouterGUI, ilUIPluginRouterGUI
 */
class srCertificateTypeGUI
{


    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var srCertificateDefinitionFormGUI
     */
    protected $form;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbar
     */
    protected $toolbar;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCertificatePlugin
     */
    protected $pl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var srCertificateType
     */
    protected $type;

    /**
     * @var ilDB
     */
    protected $db;


    public function __construct()
    {
        global $tpl, $ilCtrl, $ilToolbar, $ilTabs, $lng, $ilAccess, $ilDB;
        /** @var ilCtrl ctrl */
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        $this->type = (isset($_GET['type_id'])) ? srCertificateType::find((int)$_GET['type_id']) : null;
        $this->pl = new ilCertificatePlugin();
        $this->lng = $lng;
        $this->access = $ilAccess;
        $this->db = $ilDB;
//        $this->pl->updateLanguages();
        $this->tpl->addJavaScript($this->pl->getStyleSheetLocation('uihk_certificate.js'));
        $this->lng->loadLanguageModule('common');
        $this->ctrl->saveParameter($this, 'type_id');
        $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_cert_b.png'));
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        // needed for ILIAS >= 4.5
        if (ilCertificatePlugin::getBaseClass() != 'ilRouterGUI') {
            $this->tpl->getStandardTemplate();
        }
        switch ($next_class) {
            case '':
                switch ($cmd) {
                    case 'showTypes':
                        $this->showTypes();
                        break;
                    case 'editType':
                        $this->editType();
                        $this->setTabs('general');
                        break;
                    case 'addType':
                        $this->addType();
                        $this->setTabs('general');
                        break;
                    case 'saveType':
                        $this->saveType();
                        $this->setTabs('general');
                        break;
                    case 'editTemplate':
                        $this->editTemplate();
                        $this->setTabs('template');
                        break;
                    case 'updateTemplate':
                        $this->updateTemplate();
                        $this->setTabs('template');
                        break;
                    case 'showSettings':
                        $this->showSettings();
                        $this->setTabs('settings');
                        break;
                    case 'editSetting':
                        $this->editSetting();
                        $this->setTabs('settings');
                        break;
                    case 'updateSetting':
                        $this->updateSetting();
                        $this->setTabs('settings');
                        break;
                    case 'showPlaceholders':
                        $this->showPlaceholders();
                        $this->setTabs('placeholders');
                        break;
                    case 'addPlaceholder':
                        $this->addPlaceholder();
                        $this->setTabs('placeholders');
                        break;
                    case 'editPlaceholder':
                        $this->editPlaceholder();
                        $this->setTabs('placeholders');
                        break;
                    case 'updatePlaceholder':
                        $this->updatePlaceholder();
                        $this->setTabs('placeholders');
                        break;
                    case 'createPlaceholder':
                        $this->createPlaceholder();
                        $this->setTabs('placeholders');
                        break;
                    case '':
                        $this->showTypes();
                        break;
                }
                break;
        }
        // needed for ILIAS >= 4.5
        if (ilCertificatePlugin::getBaseClass() != 'ilRouterGUI') {
            $this->tpl->show();
        }
    }

    /**
     * Add tabs to GUI
     *
     * @param string $active_tab_id ID of activated tab
     */
    protected function setTabs($active_tab_id = 'general')
    {
        $this->tabs->addTab('general', $this->pl->txt('general'), $this->ctrl->getLinkTarget($this, 'editType'));
        if ($this->type) {
            $this->tabs->addTab('template', $this->pl->txt('template'), $this->ctrl->getLinkTarget($this, 'editTemplate'));
            $this->tabs->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'showSettings'));
            $this->tabs->addTab('placeholders', $this->pl->txt('placeholders'), $this->ctrl->getLinkTarget($this, 'showPlaceholders'));
            $this->tpl->setTitle($this->type->getTitle());
            $this->tpl->setDescription($this->type->getDescription());
        }
        $this->tabs->setTabActive($active_tab_id);
        $this->tabs->setBackTarget($this->pl->txt('back_to_overview'), $this->ctrl->getLinkTarget($this));
    }

    /**
     * Show existing certificate types in table
     */
    public function showTypes()
    {
        $this->tpl->setTitle($this->pl->txt('manage_cert_types'));
        $table = new srCertificateTypeTableGUI($this, 'showTypes');
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Show form for creating a type
     */
    public function addType()
    {
        $form = new srCertificateTypeFormGUI($this, new srCertificateType());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show form for editing a type (General)
     */
    public function editType()
    {
        $form = new srCertificateTypeFormGUI($this, $this->type);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show form for editing template settings of a type
     */
    public function editTemplate()
    {
        $form = new srCertificateTypeTemplateFormGUI($this, $this->type);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Update template related stuff
     */
    public function updateTemplate()
    {
        $form = new srCertificateTypeTemplateFormGUI($this, $this->type);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->pl->txt('msg_type_saved'), true);
            $this->ctrl->redirect($this, 'editTemplate');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * Show table with settings
     */
    public function showSettings()
    {
        $table = new srCertificateTypeSettingsTableGUI($this, 'showSettings', $this->type);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Show form for editing settings of a type
     */
    public function editSetting()
    {
        try {
            $form = new srCertificateTypeSettingFormGUI($this, $this->type, $_REQUEST['identifier']);
            $this->tpl->setContent($form->getHTML());
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'showSettings');
        }
    }

    /**
     * Update settings
     */
    public function updateSetting()
    {
        try {
            $form = new srCertificateTypeSettingFormGUI($this, $this->type, $_REQUEST['identifier']);
            if ($form->saveObject()) {
                ilUtil::sendSuccess($this->pl->txt('msg_setting_saved'), true);
                $this->ctrl->redirect($this, 'showSettings');
            } else {
                $this->tpl->setContent($form->getHTML());
            }
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'showSettings');
        }
    }

    /**
     * Show table with available placeholders for this type
     */
    public function showPlaceholders()
    {
        $table = new srCertificateTypePlaceholdersTableGUI($this, 'showPlaceholders', $this->type);
        // Show all standard placeholders below the table
        $table_std = new srCertificateTypeStandardPlaceholdersTableGUI($this, 'showPlaceholders');
        $spacer = '<div style="height: 30px;"></div>';
        $this->tpl->setContent($table->getHTML() . $spacer . $table_std->getHTML());
        $msg_info = sprintf($this->pl->txt('msg_placeholder_format_info'), srCertificatePlaceholder::PLACEHOLDER_START_SYMBOL, srCertificatePlaceholder::PLACEHOLDER_END_SYMBOL);
        ilUtil::sendInfo($msg_info);
    }

    /**
     * Add a new placeholder
     */
    public function addPlaceholder()
    {
        $placeholder = new srCertificatePlaceholder();
        $placeholder->setCertificateType($this->type);
        $form = new srCertificateTypePlaceholderFormGUI($this, $placeholder);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show form for editing a placeholder
     */
    public function editPlaceholder()
    {
        try {
            $placeholder = srCertificatePlaceholder::find($_REQUEST['placeholder_id']);
            if ($placeholder === null) {
                throw new ilException("Placeholder with ID " . $_REQUEST['placeholder_id'] . " not found");
            }
            $form = new srCertificateTypePlaceholderFormGUI($this, $placeholder);
            $this->tpl->setContent($form->getHTML());
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'showPlaceholders');
        }
    }

    /**
     * Create a new placeholder
     */
    public function createPlaceholder()
    {
        $placeholder = new srCertificatePlaceholder();
        $placeholder->setCertificateType($this->type);
        $form = new srCertificateTypePlaceholderFormGUI($this, $placeholder);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->pl->txt('msg_placeholder_saved'), true);
            $this->ctrl->redirect($this, 'showPlaceholders');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * Update placeholder
     */
    public function updatePlaceholder()
    {
        try {
            $placeholder = srCertificatePlaceholder::find($_REQUEST['placeholder_id']);
            if ($placeholder === null) {
                throw new srCertificateException("Placeholder with ID " . $_REQUEST['placeholder_id'] . " not found");
            }
            $form = new srCertificateTypePlaceholderFormGUI($this, $placeholder);
            if ($form->saveObject()) {
                ilUtil::sendSuccess($this->pl->txt('msg_placeholder_saved'), true);
                $this->ctrl->redirect($this, 'showPlaceholders');
            } else {
                $this->tpl->setContent($form->getHTML());
            }
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'showPlaceholders');
        }
    }


    /**
     * Create or update a type
     */
    public function saveType()
    {
        $type = ($this->type === NULL) ? new srCertificateType() : $this->type;
        $form = new srCertificateTypeFormGUI($this, $type);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->pl->txt('msg_type_saved'), true);
            $this->ctrl->setParameter($this, 'type_id', $type->getId());
            $this->ctrl->redirect($this, 'editType');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


}