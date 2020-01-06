<?php	return array (
  '平台介绍/:id' => 
  array (
    0 => 'portal/Article/index?cid=1',
    1 => 
    array (
    ),
    2 => 
    array (
      'id' => '\d+',
      'cid' => '\d+',
    ),
  ),
  '平台介绍' => 
  array (
    0 => 'portal/List/index?id=1',
    1 => 
    array (
    ),
    2 => 
    array (
      'id' => '\d+',
    ),
  ),
  '1234abcd$' => 'admin/Index/index',
  'city' => 'api/Address/getCity',
  'area/:city' => 'api/Address/getCityId',
  'workType/:auth' => 'api/Work/getWorkType',
  'workList' => 'api/Work/getWork',
  'recommendWork' => 'api/Work/recommendWork',
  'workInfo/:id' => 'api/Work/getWorkInfo',
  'login' => 'api/Login/login',
  'customerMobile' => 'api/Work/getPlatformMobile',
  'platformInfo' => 'api/Work/getPlatformInfo',
  'userInfo' => 'api/Account/getUserInfo',
  'editUser' => 'api/Account/editUserInfo',
  'changeAvatar' => 'api/Account/uploadAvatar',
  'idea' => 'api/Account/addUserIdea',
  'joinUs' => 'api/Account/joinUs',
  'follow/:id' => 'api/Account/followWork',
  'send/:id' => 'api/Account/sendWork',
  'search' => 'api/Account/searchWork',
  'searchHistory' => 'api/Account/searchHistory',
  'clearSearch' => 'api/Account/clearSearch',
  'clock' => 'api/Account/getClock',
  'clockList' => 'api/Account/getClockList',
  'followList' => 'api/Account/getFollowList',
  'likeList' => 'api/Account/getLikeList',
  'job' => 'api/Account/getJobInfo',
  'sendList' => 'api/Account/sendWorkList',
  'dealSend' => 'api/Account/dealAllSend',
  'sendInfo' => 'api/Account/sendWorkInfo',
  'salaryList' => 'api/Account/getSalaryList',
  'salary' => 'api/Account/getSalary',
  'newMessage' => 'api/Account/getNewMessage',
  'sendMessage' => 'api/Mobile/sendMessage',
  'checkMessage' => 'api/Mobile/checkMessage',
);