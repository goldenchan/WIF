<?php /* Smarty version Smarty-3.1.13, created on 2014-09-07 10:28:37
         compiled from "/Users/golden/Sites/github/WIF/application/templates/default/Default/index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1845932779540ae30ba99f19-01030674%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7f93239b0cb5f729e6b6b59c32cd5105591c292a' => 
    array (
      0 => '/Users/golden/Sites/github/WIF/application/templates/default/Default/index.tpl',
      1 => 1410056914,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1845932779540ae30ba99f19-01030674',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_540ae30bab5cf2_47161973',
  'variables' => 
  array (
    'c_props' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_540ae30bab5cf2_47161973')) {function content_540ae30bab5cf2_47161973($_smarty_tpl) {?><html>
<head>
<title>首页</title>
</head>
<body>
It works!
<?php echo json_encode($_smarty_tpl->tpl_vars['c_props']->value);?>

<!-- Simple_View <<?php ?>?php echo json_encode($c_props); ?<?php ?>> -->
<!-- Smarty_View <?php echo json_encode($_smarty_tpl->tpl_vars['c_props']->value);?>
 -->
<!--<<?php ?>?php $this->includeTpl('layout/menu');?<?php ?>>-->
</body>
</html>
<?php }} ?>