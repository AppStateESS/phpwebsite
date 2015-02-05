<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($data['salt'])) {
    $data['authkey'] = \Current_User::getAuthKey($data['salt']);
} else {
    $data['authkey'] = \Current_User::getAuthKey();
}