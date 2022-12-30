<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'AdminController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['individuals'] = 'AdminController/allIndividuals';
$route['agents'] = 'AdminController/allAgents';
$route['add-user'] = 'AdminController/addUser';
$route['login'] = 'AdminController/login';
$route['logout'] = 'AdminController/logout';
$route['getuserprofile']['POST'] = 'AdminController/getUser';
$route['deleteuserprofile']['POST'] = 'AdminController/deleteUser';
$route['approveuserprofile']['POST'] = 'AdminController/approveUser';
$route['activateuserprofile']['POST'] = 'AdminController/activateUserToggle';
$route['approveproperties']['POST'] = 'AdminController/approveProperties';
$route['profile'] = 'AdminController/profile';
$route['properties/amenities'] = 'AdminController/amenities';
$route['properties/types'] = 'AdminController/types';
$route['addAmenities'] = 'AdminController/addAmenities';
$route['addType'] = 'AdminController/addType';
$route['deletetype/(:num)'] = 'AdminController/deletetype/$1';
$route['deleteamenity/(:num)'] = 'AdminController/deleteamenity/$1';
$route['deleteamenity/(:num)'] = 'AdminController/deleteamenity/$1';
$route['updatetype'] = 'AdminController/updatetype';
$route['updateamenity'] = 'AdminController/updateamenity';
$route['contactinfo'] = 'AdminController/contactinfo';
$route['updateUserSession'] = 'AdminController/updateUserSession';


$route['properties'] = 'AdminController/allProperties';
$route['getproperty']['POST'] = 'AdminController/getProperty';
$route['deleteproperty']['POST'] = 'AdminController/deleteProperty';
$route['activateProperty'] = 'AdminController/activatePropertyToggle';

$route['getnotifications']['GET'] = 'AdminController/getNotifications';
$route['notifications'] = 'AdminController/allNotifications';



