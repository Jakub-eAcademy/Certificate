<?php

/**
 * Table class srCertificateTypeSignaturesTableGUI
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @version           $Id:
 **/
class srCertificateTypeSignaturesTableGUI extends ilTable2GUI
{

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilCertificatePlugin
     */
    protected $pl;
    /**
     * @var srCertificateType
     */
    protected $type;
    /**
     * @var array
     */
    protected $columns = array(
        'last_name',
        'first_name'
    );

    /**
     * @param                   $a_parent_obj
     * @param string            $a_parent_cmd
     * @param srCertificateType $type
     */
    public function __construct($a_parent_obj, $a_parent_cmd, srCertificateType $type)
    {
        global $DIC;
        $this->setId('cert_type_signatures');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->type = $type;
        $this->pl = ilCertificatePlugin::getInstance();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->setRowTemplate('tpl.type_signatures_row.html', $this->pl->getDirectory());
        $this->initColumns();
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $button = ilLinkButton::getInstance();
        $button->setCaption($this->pl->txt('add_new_signature'), false);
        $button->setUrl($this->ctrl->getLinkTarget($a_parent_obj, srCertificateTypeGUI::CMD_ADD_SIGNATURE));
        $this->toolbar->addButtonInstance($button);
        $this->buildData();
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set):void
    {
        $skip_fields = array('id');
        foreach ($a_set as $k => $v) {
            if (in_array($k, $skip_fields)) {
                continue;
            }
            $this->tpl->setCurrentBlock('td');
            $this->tpl->setVariable('TD_VALUE', $v);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('ACTIONS', $this->buildActionMenu($a_set)->getHTML());
    }

    /**
     * Build action menu
     * @param array $a_set
     * @return ilAdvancedSelectionListGUI
     */
    protected function buildActionMenu(array $a_set)
    {
        $list = new ilAdvancedSelectionListGUI();
        $list->setId($a_set['id']);
        $list->setListTitle($this->pl->txt('actions'));
        $this->ctrl->setParameterByClass(srCertificateTypeGUI::class, 'type_id', $this->type->getId());
        $this->ctrl->setParameterByClass(srCertificateTypeGUI::class, 'signature_id', $a_set['id']);
        $list->addItem($this->pl->txt('edit'), 'edit',
            $this->ctrl->getLinkTargetByClass(srCertificateTypeGUI::class, srCertificateTypeGUI::CMD_EDIT_SIGNATURE));
        $list->addItem($this->pl->txt('delete'), 'delete',
            $this->ctrl->getLinkTargetByClass(srCertificateTypeGUI::class,
                srCertificateTypeGUI::CMD_CONFIRM_DELETE_SIGNATURE));
        $list->addItem($this->pl->txt('download'), 'download',
            $this->ctrl->getLinkTargetByClass(srCertificateTypeGUI::class,
                srCertificateTypeGUI::CMD_DOWNLOAD_SIGNATURE));

        return $list;
    }

    /**
     * Add columns
     */
    protected function initColumns()
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->pl->txt($column), $column);
        }
        $this->addColumn($this->pl->txt('actions'));
    }

    /**
     * Get settings
     */
    protected function buildData()
    {
        $data = array();
        /** @var $signature srCertificateSignature */
        foreach ($this->type->getSignatures() as $signature) {
            $row = array();
            $row['id'] = $signature->getId();
            $row['last_name'] = $signature->getLastName();
            $row['first_name'] = $signature->getFirstName();
            $data[] = $row;
        }
        $this->setData($data);
    }
}
