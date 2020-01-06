<?php
/**
 * 获取当前登录的管理员ID
 * @return int
 */
function cmf_get_current_company_id()
{
    return session('ADMIN_ID');

}