<?php
require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');
require_once('class.ilCertificateConfig.php');
require_once('class.srCertificateHooks.php');

/**
 * Certificate Plugin
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilCertificatePlugin extends ilUserInterfaceHookPlugin
{

    /**
     * Name of class that can implement hooks
     */
    const CLASS_NAME_HOOKS = 'srCertificateCustomHooks';

    /**
     * Default path for hook class (can be changed in plugin config)
     */
    const DEFAULT_PATH_HOOK_CLASS = './Customizing/global/Certificate/';

    /**
     * Default formats (can be changed in plugin config)
     */
    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d, H:i';

    /**
     * @var srCertificateHooks
     */
    protected $hooks;

    /**
     * This will be ilRouterGUI for ILIAS <= 4.4.x if the corresponding Router service is installed
     * and ilUIPluginRouterGUI for ILIAS >= 4.5.x
     *
     * @var string
     */
    protected static $base_class;

    /**
     * @return string
     */
    public function getPluginName()
    {
        return 'Certificate';
    }


    public function init()
    {
        // Include ActiveRecord base class
        if (is_file('./Services/ActiveRecord/class.ActiveRecord.php')) {
            include_once('./Services/ActiveRecord/class.ActiveRecord.php');
        } elseif (is_file('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php')) {
            include_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
        } else {
            throw new ilException("Certificate plugin needs ActiveRecord (https://github.com/studer-raimann/ActiveRecord) or ILIAS 4.5");
        }
    }


    /**
     * @return ilCertificateConfig
     */
    public function getConfigObject()
    {
        $conf = new ilCertificateConfig($this->getConfigTableName());
        return $conf;
    }

    /**
     * Get Hooks object
     *
     * @return srCertificateHooks
     */
    public function getHooks()
    {
        if (is_null($this->hooks)) {
            $class_name = self::CLASS_NAME_HOOKS;
            $path = $this->getConfigObject()->getValue('path_hook_class');
            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
            $file = $path . "class.{$class_name}.php";
            if (is_file($file)) {
                require_once($file);
                $object = new $class_name($this);
            } else {
                $object = new srCertificateHooks($this);
            }
            $this->hooks = $object;
        }
        return $this->hooks;
    }

    /**
     * @return string
     */
    public function getConfigTableName()
    {
        return
            $this->getSlotId() . substr(strtolower($this->getPluginName()), 0, 20 - strlen($this->getSlotId())) . '_c';
    }


    /**
     * Check if course is a "template course"
     * This method returns true if the given ref-ID is a children of a category defined in the plugin options
     *
     * @param int $ref_id Ref-ID of the object to check
     * @return bool
     */
    public function isCourseTemplate($ref_id)
    {
        global $tree;

        $config = $this->getConfigObject();
        if ($config->getValue('course_templates') && $config->getValue('course_templates_ref_ids')) {
            // Course templates enabled -> check if given ref_id is defined as template
            $ref_ids = explode(',', $config->getValue('course_templates_ref_ids'));
            /** @var $tree ilTree */
            $parent_ref_id = $tree->getParentId($ref_id);
            return in_array($parent_ref_id, $ref_ids);
        }
        return false;
    }

    /**
     * Check if preconditions are given to use this plugin
     *
     * @return bool
     */
    public function checkPreConditions()
    {
        global $ilPluginAdmin;

        /** @var $ilPluginAdmin ilPluginAdmin */
        $exists = $ilPluginAdmin->exists(IL_COMP_SERVICE, 'EventHandling', 'evhk', 'CertificateEvents');
        $active = $ilPluginAdmin->isActive(IL_COMP_SERVICE, 'EventHandling', 'evhk', 'CertificateEvents');
        return (self::getBaseClass() && $exists && $active);
    }

    /**
     * Don't activate plugin if preconditions are not given
     *
     * @return bool
     */
    protected function beforeActivation()
    {
        if (!$this->checkPreConditions()) {
            ilUtil::sendFailure("You need to install the 'CertificateEvents' plugin");
            return false;
        }
        return true;
    }


    /**
     * Returns in what class the command/ctrl chain should start for this plugin.
     * Return value is ilRouterGUI for ILIAS <= 4.4.x, ilUIPluginRouterGUI for ILIAS >= 4.5, of false otherwise
     *
     * @return bool|string
     */
    public static function getBaseClass()
    {
        if (!is_null(self::$base_class)) {
            return self::$base_class;
        }

        global $ilCtrl;
        if ($ilCtrl->lookupClassPath('ilUIPluginRouterGUI')) {
            self::$base_class = 'ilUIPluginRouterGUI';
        } elseif($ilCtrl->lookupClassPath('ilRouterGUI')) {
            self::$base_class = 'ilRouterGUI';
        } else {
            self::$base_class = false;
        }

        return self::$base_class;
    }
}

?>
