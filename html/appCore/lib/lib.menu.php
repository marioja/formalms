<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class MenuManager
{
    public $acl;

    public $acl_man;

    public function __construct()
    {
        $this->acl = \FormaLms\lib\Forma::getAclManager();
        $this->acl_man = \FormaLms\lib\Forma::getAclManager();
    }

    public function addPerm($groupid, $roleid)
    {
        $group = $this->acl_man->getGroup(false, $groupid);
        $idst_group = $group[ACL_INFO_IDST];
        $role = $this->acl_man->getRole(false, $roleid);
        $id_role = $role[ACL_INFO_IDST];
        $this->acl_man->addToRole($id_role, $idst_group);
    }

    public function removePerm($groupid, $roleid)
    {
        $group = $this->acl_man->getGroup(false, $groupid);
        $idst_group = $group[ACL_INFO_IDST];
        $role = $this->acl_man->getRole(false, $roleid);
        $id_role = $role[ACL_INFO_IDST];
        $this->acl_man->removeFromRole($id_role, $idst_group);
    }
}
