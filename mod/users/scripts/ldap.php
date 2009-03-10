<?php
/**
 * This script was modified from a working script. You will need
 * to play with the settings to get to operate. 
 * Check this site for LDAP binding information:
 * http://www.rlmueller.net/LDAP_Binding.htm
 * @author Tommy De Jesus
 * @modified Matthew McNaney
 */

class ldap_authorization extends User_Authorization {
    public $host            = '';
    public $port            = '389';
    public $ldap_dn         = 'cn=common_name,';
    public $ldap_dn2        = 'ou=organization_unit,dc=my_ldap_site,dc=com';
    public $ldap_base       = 'ou=organization_unit,dc=my_ldap_site,dc=com';
    public $bind_password   = 'bind_password';
    public $bind            = null;

    public $create_new_user = true;
    public $show_login_form = true;
    // Authorize on local database just once
    public $always_verify   = false;
    public $force_login     = false;
    public $login_link      = 'index.php?module=users&action=user&command=login_page';
    public $logout_link     = 'index.php?module=users&action=user&command=logout';

    public function authenticate()
    {
        if (empty($this->password)) {
            return false;
        }
        $connection = ldap_connect($this->host, $this->port)
            or die(sprintf(dgettext('users', 'Could not connect to %s'), $this->host));

        $this->bind = @ldap_bind($connection, $this->ldap_dn, $this->bind_password);

        if (empty($this->bind)) {
            return false;
        } else {
            $attributes = array("cn");
            $filter = sprintf('(sAMAccountName=%s)', $this->user->username);
            $result = ldap_search($connection, $this->ldap_base, $filter, $attributes);
            $entries = ldap_get_entries($connection, $result);
            $uid = $entries[0]["cn"][0];
            ldap_unbind($connection);
            $connection = ldap_connect( $this->host, $this->port );

            $bind2 = @ldap_bind($connection, 'cn=' . $uid . ',' . $this->ldap_dn2, $this->password);

            if(empty($bind2)) {
                return FALSE;
            } else {
                ldap_unbind($connection);
                return TRUE;
            }
        }
    }

    public function verify()
    {
        return ($this->user->id && $this->user->_logged);
    }

    public function createUser(){}
    public function logout(){}
}
?>
