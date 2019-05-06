<?php 
$router->post('abnormal/correct_box_express_no', 'AbnormalController@correctBoxExpressNo');//矫正箱码与子单号的关联
$router->post('abnormal/correct_order_address_md5', 'AbnormalController@correctOrderAddressMd5');//矫正分仓合单地址标记
$router->post('abnormal/manual_save_agency_over', 'AbnormalController@manualSaveAgencyOver');//手动收货结束

?>