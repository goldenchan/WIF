<html>
<head>
<title>首页</title>
</head>
<body>
It works!
<?php echo json_encode($c_props); ?>
<!-- Simple_View <?php echo json_encode($c_props); ?> -->
<!-- Smarty_View {$c_props|json_encode} -->
<!--<?php $this->includeTpl('layout/menu');?>-->
</body>
</html>
