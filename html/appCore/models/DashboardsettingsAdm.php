<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * Class DashboardsettingsAdm
 */
class DashboardsettingsAdm extends Model
{
    protected $db;

    protected $enabledBlocks;

    protected $installedBlocks;

    protected $layouts;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->loadLayouts();
        $this->loadInstalledBlocks();
        $this->loadEnabledBlocks();
    }

    public function loadLayouts()
    {
        $query = "SELECT `id`, `name`, `caption`, `status`, `default` FROM `dashboard_layouts` ORDER BY `default` DESC, `created_at` ASC";

        $result = sql_query($query);
        $this->layouts = [];

        while ($layout = sql_fetch_object($result)) {
            /** @var DashboardLayoutLms $layoutObj */
            $layoutObj = new DashboardLayoutLms();
            $layoutObj->setId($layout->id);
            $layoutObj->setName($layout->name);
            $layoutObj->setCaption($layout->caption);
            $layoutObj->setStatus($layout->status);
            $layoutObj->setDefault($layout->default);

            $this->layouts[] = $layoutObj;
        }
    }

    public function getLayout($id)
    {
        $query = "SELECT `id`, `name`, `caption`, `status`, `default` FROM `dashboard_layouts` WHERE id = $id";
        $result = sql_query($query);

        return sql_fetch_object($result);
    }

    public function loadEnabledBlocks()
    {
        $query_blocks = "SELECT `id`, `block_class`, `block_config`, `position`, `dashboard_id` FROM `dashboard_block_config` ORDER BY `position` ASC";

        $result = $this->db->query($query_blocks);

        while ($block = $this->db->fetch_assoc($result)) {
            /** @var DashboardBlockLms $blockObj */
            $blockObj = new $block['block_class']($block['block_config']);
            $blockObj->setOrder($block['position']);

            $this->enabledBlocks[$block['dashboard_id']][] = $blockObj;
        }
    }

    public function loadInstalledBlocks()
    {

        $query_blocks = "SELECT `id`, `block_class` FROM `dashboard_blocks`";

        $result = $this->db->query($query_blocks);

        while ($block = $this->db->fetch_assoc($result)) {

            require_once Forma::inc(_lms_ . '/models/' . $block['block_class'] . '.php');
            /** @var DashboardBlockLms $blockObj */
            $blockObj = new $block['block_class']('');

            $this->installedBlocks[] = $blockObj;
        }
    }

    /**
     * @return mixed
     */
    public function getEnabledBlocks()
    {
        return $this->enabledBlocks;
    }

    /**
     * @return mixed
     */
    public function getInstalledBlocks()
    {
        return $this->installedBlocks;
    }

    public function getEnabledBlocksCommonViewData($dashboardId = false)
    {
        $data = [];
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->enabledBlocks)) {
            /** @var DashboardBlockLms $enabledBlocks */
            foreach ($this->enabledBlocks[$dashboardId] as $enabledBlocks) {
                $data[] = $enabledBlocks->getSettingsCommonViewData();
            }
        }

        return $data;
    }

    public function getInstalledBlocksCommonViewData()
    {
        $data = [];
        /** @var DashboardBlockLms $installedBlock */
        foreach ($this->installedBlocks as $installedBlock) {
            $data[] = $installedBlock->getSettingsCommonViewData();
        }

        return $data;
    }

    public function resetOldSettings($dashboard)
    {
        $query_blocks = sprintf("DELETE FROM dashboard_block_config WHERE `dashboard_id` = %s;", $dashboard);

        $this->db->query($query_blocks);
    }

    public function saveLayout($layout)
    {
        $name = $layout['name'];
        $caption = $layout['caption'] ?: ' ';
        $status = $layout['status'];

        $query = "SELECT COUNT(*) AS count FROM `dashboard_layouts`";
        $res = $this->db->query($query);
        $res = sql_fetch_array($res);
        $default = $res['count'] ? 0 : 1;

        $sql = "
            INSERT INTO `dashboard_layouts` ( `name`, `caption`, `status`, `default`, `created_at`) 
            VALUES ( '" . addslashes($name) . "', '" . addslashes($caption) . "', '" . addslashes($status) . "', " . $default . ", CURRENT_TIMESTAMP)";

        return sql_query($sql);
    }

    public function editInlineLayout($data)
    {
        $query = "UPDATE `dashboard_layouts` SET " . $data['col'] . " = '" . addslashes($data['new_value']) . "' WHERE id = " . $data['id'];
        return $this->db->query($query);
    }

    public function delLayout($id_layout)
    {
        $query = "DELETE FROM `dashboard_layouts` WHERE id = $id_layout";
        return $this->db->query($query);
    }

    public function defaultLayout($id_layout)
    {
        $query = "UPDATE `dashboard_layouts` SET `default` = 0";
        $this->db->query($query);

        $query = "UPDATE `dashboard_layouts` SET `default` = 1, `status` = 'publish' WHERE id = $id_layout";
        return $this->db->query($query);
    }

    public function saveBlockSetting($block, $setting, $dashboard)
    {
        $config = [
            'type' => $setting['type'],
            'enabled' => $setting['enabled'],
            'enabledActions' => $setting['enabledActions'],
            'data' => $setting['data']
        ];

        $insertQuery = sprintf("INSERT INTO `dashboard_block_config` ( `block_class`, `block_config`, `position`, `dashboard_id`, `created_at`) VALUES ( '%s' , '%s', '%s', '%s', CURRENT_TIMESTAMP)", $block, json_encode($config), $setting['position'], $dashboard);
        $this->db->query($insertQuery);
    }

    /**
     * @return mixed
     */
    public function getLayouts()
    {
        return $this->layouts;
    }
}
