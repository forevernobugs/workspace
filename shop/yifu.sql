/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : yifu

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2019-05-15 00:37:07
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for shop_apiconfig
-- ----------------------------
DROP TABLE IF EXISTS `shop_apiconfig`;
CREATE TABLE `shop_apiconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `key` varchar(255) NOT NULL COMMENT '配置项名称',
  `value` varchar(255) NOT NULL COMMENT '配置项值',
  `description` varchar(255) DEFAULT NULL COMMENT '配置描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='API接口配置表';

-- ----------------------------
-- Records of shop_apiconfig
-- ----------------------------
INSERT INTO `shop_apiconfig` VALUES ('1', 'print_apikey', '', 'API密钥');
INSERT INTO `shop_apiconfig` VALUES ('2', 'print_machine_code', '', '打印机终端号');
INSERT INTO `shop_apiconfig` VALUES ('3', 'print_msign', '', '打印机密钥');
INSERT INTO `shop_apiconfig` VALUES ('4', 'print_mobiliphone', '', '终端内部手机号');
INSERT INTO `shop_apiconfig` VALUES ('5', 'print_partner', '', '易连云用户ID');
INSERT INTO `shop_apiconfig` VALUES ('6', 'print_username', '', '易连云用户名');
INSERT INTO `shop_apiconfig` VALUES ('7', 'print_printname', '', '打印机终端名称');
INSERT INTO `shop_apiconfig` VALUES ('8', 'sms_appkey', '', 'Key值');
INSERT INTO `shop_apiconfig` VALUES ('9', 'sms_appsecret', '', '密钥');
INSERT INTO `shop_apiconfig` VALUES ('10', 'sms_template_code', '', '模板ID');
INSERT INTO `shop_apiconfig` VALUES ('11', 'sms_signname', '', '签名名称');
INSERT INTO `shop_apiconfig` VALUES ('12', 'alipay_partner', '', '合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串');
INSERT INTO `shop_apiconfig` VALUES ('13', 'alipay_appkey', '', ' MD5密钥，安全检验码，由数字和字母组成的32位字符串');
INSERT INTO `shop_apiconfig` VALUES ('14', 'wechat_appid', '', '微信公众号身份的唯一标识');
INSERT INTO `shop_apiconfig` VALUES ('15', 'wechat_mchid', '', '受理商ID，身份标识');
INSERT INTO `shop_apiconfig` VALUES ('16', 'wechat_appkey', '', '商户支付密钥Key');
INSERT INTO `shop_apiconfig` VALUES ('17', 'wechat_appsecret', '', 'JSAPI接口中获取openid');
INSERT INTO `shop_apiconfig` VALUES ('18', 'wechat_token', '', '微信通讯token值');

-- ----------------------------
-- Table structure for shop_banner
-- ----------------------------
DROP TABLE IF EXISTS `shop_banner`;
CREATE TABLE `shop_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '广告名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '广告位置描述',
  `position` int(11) NOT NULL COMMENT '广告位置',
  `banner_path` varchar(140) NOT NULL COMMENT '图片地址',
  `link` varchar(140) NOT NULL DEFAULT '' COMMENT '连接地址',
  `level` int(4) NOT NULL DEFAULT '0' COMMENT '优先级',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（2：禁用 1：正常）',
  `createtime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='首页轮播图保存';

-- ----------------------------
-- Records of shop_banner
-- ----------------------------
INSERT INTO `shop_banner` VALUES ('1', 'banner图1', 'banner图1', '1', '/uploads/picture/20190506/34a4fefb125f1c436c96356ce2dca11f.jpg', '#', '0', '1', '1551886458', '0');
INSERT INTO `shop_banner` VALUES ('2', 'banner图2', 'banner图2', '1', '/uploads/picture/20190506/4e87ca5818bd5a7f229c151ac671621d.jpg', '#', '0', '1', '1551886578', '0');
INSERT INTO `shop_banner` VALUES ('3', 'banner图3', 'banner图3', '1', '/uploads/picture/20190506/10d2dab9801b5b1b0c7759d24f75f044.jpg', '#', '0', '1', '1551886698', '0');
INSERT INTO `shop_banner` VALUES ('4', '推荐商品', '推荐商品', '2', '/uploads/picture/20190506/f3aa19ed42484e2ff467a5d51653d748.jpg', '#', '0', '1', '1551886933', '0');

-- ----------------------------
-- Table structure for shop_banner_position
-- ----------------------------
DROP TABLE IF EXISTS `shop_banner_position`;
CREATE TABLE `shop_banner_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(80) NOT NULL,
  `width` char(20) NOT NULL,
  `height` char(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(0:禁用 1：正常)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='轮播图属性表';

-- ----------------------------
-- Records of shop_banner_position
-- ----------------------------
INSERT INTO `shop_banner_position` VALUES ('1', 'pc首页banner图', '300', '1190', '1');
INSERT INTO `shop_banner_position` VALUES ('2', '商品页推荐', '200', '260', '1');

-- ----------------------------
-- Table structure for shop_cart
-- ----------------------------
DROP TABLE IF EXISTS `shop_cart`;
CREATE TABLE `shop_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `createtime` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常，2：已购买',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='购物车';

-- ----------------------------
-- Records of shop_cart
-- ----------------------------
INSERT INTO `shop_cart` VALUES ('2', '3', '1', '5', '1549977418', '1');
INSERT INTO `shop_cart` VALUES ('3', '4', '1', '1', '1549977898', '1');
INSERT INTO `shop_cart` VALUES ('4', '5', '1', '1', '1550661297', '1');
INSERT INTO `shop_cart` VALUES ('5', '6', '2', '1', '1550664864', '1');
INSERT INTO `shop_cart` VALUES ('6', '7', '1', '4', '1555204892', '1');
INSERT INTO `shop_cart` VALUES ('9', '1', '1', '1', '1556868632', '1');
INSERT INTO `shop_cart` VALUES ('10', '1', '4', '3', '1557155290', '1');
INSERT INTO `shop_cart` VALUES ('11', '10', '4', '1', '1557851286', '1');

-- ----------------------------
-- Table structure for shop_code
-- ----------------------------
DROP TABLE IF EXISTS `shop_code`;
CREATE TABLE `shop_code` (
  `id` int(60) NOT NULL AUTO_INCREMENT,
  `mobile` char(128) DEFAULT NULL,
  `code` char(30) DEFAULT NULL,
  `yzm_time` int(60) DEFAULT NULL,
  `num` int(60) NOT NULL DEFAULT '0',
  `captcha` char(30) NOT NULL,
  `date` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_code
-- ----------------------------

-- ----------------------------
-- Table structure for shop_email_check
-- ----------------------------
DROP TABLE IF EXISTS `shop_email_check`;
CREATE TABLE `shop_email_check` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `username` char(128) NOT NULL,
  `email` char(128) NOT NULL,
  `passtime` int(128) NOT NULL,
  `token` char(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_email_check
-- ----------------------------

-- ----------------------------
-- Table structure for shop_goods
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods`;
CREATE TABLE `shop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `num` int(11) NOT NULL COMMENT '商品库存数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `description` text NOT NULL COMMENT '商品描述',
  `standard` varchar(255) NOT NULL COMMENT '规格型号',
  `cover_path` varchar(255) NOT NULL COMMENT '封面图',
  `photo_path_1` varchar(255) DEFAULT NULL,
  `photo_path_2` varchar(255) DEFAULT NULL,
  `photo_path_3` varchar(255) DEFAULT NULL,
  `content` text NOT NULL COMMENT '商品详情',
  `click_count` int(11) NOT NULL DEFAULT '0' COMMENT '商品点击数',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:上架，2：下架',
  `is_best` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为精品',
  `is_new` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为新品',
  `is_hot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为热销',
  `sell_num` int(11) NOT NULL DEFAULT '0' COMMENT '已经出售的数量',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  `score_num` tinyint(2) NOT NULL DEFAULT '1' COMMENT '平均评分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='商品表';

-- ----------------------------
-- Records of shop_goods
-- ----------------------------
INSERT INTO `shop_goods` VALUES ('4', '1', '8a11f0df-0ede-6857-7fd6-675efc62d037', 'nake', '100', '1000.00', 'nake', 'M', '/uploads/picture/20190506/e7187b6feda1c523ad3b0a6584b09271.jpg', '/uploads/picture/20190506/4abafdf75dc28d110079f5e354bc4410.jpg', '/uploads/picture/20190506/ca169728a37b85703fc585b7a4ca9699.jpg', '', 'nake<audio controls=\"controls\" style=\"display: none;\"></audio>', '0', '1', '1', '1', '1', '0', '1557154820', '1');

-- ----------------------------
-- Table structure for shop_goods_cate
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods_cate`;
CREATE TABLE `shop_goods_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '分类名',
  `slug` varchar(200) NOT NULL COMMENT '缩略名',
  `cover_path` varchar(200) NOT NULL COMMENT '分类封面图',
  `pid` int(11) NOT NULL DEFAULT '0',
  `page_num` int(11) NOT NULL,
  `lists_tpl` varchar(200) NOT NULL,
  `detail_tpl` varchar(200) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:启用，2：禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='商品分类';

-- ----------------------------
-- Records of shop_goods_cate
-- ----------------------------
INSERT INTO `shop_goods_cate` VALUES ('1', '衣服', '衣服', '', '0', '20', 'goods_list', 'goods_detail', '1');
INSERT INTO `shop_goods_cate` VALUES ('3', 'nake', 'nake', '', '1', '20', 'goods_list', 'goods_detail', '1');

-- ----------------------------
-- Table structure for shop_goods_cate_relationships
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods_cate_relationships`;
CREATE TABLE `shop_goods_cate_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `cate_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='商品-分类关系表';

-- ----------------------------
-- Records of shop_goods_cate_relationships
-- ----------------------------
INSERT INTO `shop_goods_cate_relationships` VALUES ('1', '1', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('3', '3', '4');
INSERT INTO `shop_goods_cate_relationships` VALUES ('4', '2', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('5', '1', '5');
INSERT INTO `shop_goods_cate_relationships` VALUES ('6', '1', '6');
INSERT INTO `shop_goods_cate_relationships` VALUES ('7', '2', '7');
INSERT INTO `shop_goods_cate_relationships` VALUES ('8', '3', '6');
INSERT INTO `shop_goods_cate_relationships` VALUES ('9', '1', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('10', '2', '4');
INSERT INTO `shop_goods_cate_relationships` VALUES ('11', '3', '1');
INSERT INTO `shop_goods_cate_relationships` VALUES ('12', '3', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('13', '1', '1');
INSERT INTO `shop_goods_cate_relationships` VALUES ('14', '1', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('15', '1', '1');
INSERT INTO `shop_goods_cate_relationships` VALUES ('16', '2', '2');
INSERT INTO `shop_goods_cate_relationships` VALUES ('17', '3', '1');
INSERT INTO `shop_goods_cate_relationships` VALUES ('18', '4', '3');

-- ----------------------------
-- Table structure for shop_goods_collection
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods_collection`;
CREATE TABLE `shop_goods_collection` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL COMMENT '用户id',
  `goods_id` int(10) DEFAULT NULL COMMENT '商品id',
  `createtime` varchar(11) DEFAULT NULL COMMENT '收藏时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户收藏商品表';

-- ----------------------------
-- Records of shop_goods_collection
-- ----------------------------
INSERT INTO `shop_goods_collection` VALUES ('1', '5', '1', '1550661435');
INSERT INTO `shop_goods_collection` VALUES ('2', '6', '2', '1550664919');
INSERT INTO `shop_goods_collection` VALUES ('3', '1', '2', '1556868355');

-- ----------------------------
-- Table structure for shop_goods_comment
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods_comment`;
CREATE TABLE `shop_goods_comment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增唯一ID',
  `uid` int(20) DEFAULT NULL,
  `goods_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '对应文章ID',
  `order_id` varchar(20) DEFAULT NULL COMMENT '订单号',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '评论时间',
  `content` text NOT NULL COMMENT '评论正文',
  `approved` varchar(20) NOT NULL DEFAULT '0' COMMENT '审核 0-待审核  1-已审核',
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父评论ID',
  `score` int(2) DEFAULT NULL COMMENT '商品评分',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 -1-删除  1-正常',
  PRIMARY KEY (`id`),
  KEY `comment_post_ID` (`goods_id`),
  KEY `comment_approved_date_gmt` (`approved`),
  KEY `comment_parent` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='商品评论表';

-- ----------------------------
-- Records of shop_goods_comment
-- ----------------------------
INSERT INTO `shop_goods_comment` VALUES ('1', '4', '1', '5', '1549978033', 'dasdas', '1', '0', '4', '1');
INSERT INTO `shop_goods_comment` VALUES ('2', '5', '1', '6', '1550661353', 'eqweqweqw', '1', '0', '4', '1');
INSERT INTO `shop_goods_comment` VALUES ('3', '6', '2', '7', '1550664903', 'dsadas', '1', '0', '5', '1');
INSERT INTO `shop_goods_comment` VALUES ('4', '7', '1', '1', '1555204992', '33', '1', '0', '5', '-1');
INSERT INTO `shop_goods_comment` VALUES ('5', '1', '1', '4', '1555207498', '深Vweb', '0', '0', '5', '-1');
INSERT INTO `shop_goods_comment` VALUES ('6', '1', '2', '6', '1556868323', '3123223.', '1', '0', '5', '1');

-- ----------------------------
-- Table structure for shop_key_value
-- ----------------------------
DROP TABLE IF EXISTS `shop_key_value`;
CREATE TABLE `shop_key_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection` varchar(128) NOT NULL COMMENT '命名集合键和值对',
  `uuid` varchar(128) NOT NULL DEFAULT 'default' COMMENT '系统唯一标识',
  `name` varchar(128) NOT NULL COMMENT '键名',
  `value` longtext NOT NULL COMMENT 'The value.',
  PRIMARY KEY (`id`,`collection`,`uuid`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_key_value
-- ----------------------------
INSERT INTO `shop_key_value` VALUES ('1', 'config.base', 'default', 'web_allow_register', '1');
INSERT INTO `shop_key_value` VALUES ('2', 'config.base', 'default', 'web_site_close', '0');
INSERT INTO `shop_key_value` VALUES ('3', 'config.base', 'default', 'web_site_description', '商城');
INSERT INTO `shop_key_value` VALUES ('4', 'config.base', 'default', 'web_site_icp', '');
INSERT INTO `shop_key_value` VALUES ('5', 'config.base', 'default', 'web_site_keyword', '商城');
INSERT INTO `shop_key_value` VALUES ('6', 'config.base', 'default', 'web_site_title', '商城');
INSERT INTO `shop_key_value` VALUES ('7', 'config.base', 'default', 'web_allow_ticket', '0');
INSERT INTO `shop_key_value` VALUES ('8', 'indextheme', 'default', 'name', 'default');
INSERT INTO `shop_key_value` VALUES ('9', 'posts.form', '9db99141-65a4-2393-bfa8-d4d100e1a1f4', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('10', 'posts.form', '1d3fa553-6e07-eed6-f459-4694de378122', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('11', 'term.taxonomy', '1caad667-985e-4b91-ef4a-fbbac872fbce', 'page_num', '20');
INSERT INTO `shop_key_value` VALUES ('12', 'term.taxonomy', '1caad667-985e-4b91-ef4a-fbbac872fbce', 'lists_tpl', 'news_list');
INSERT INTO `shop_key_value` VALUES ('13', 'term.taxonomy', '1caad667-985e-4b91-ef4a-fbbac872fbce', 'detail_tpl', 'news_detail');
INSERT INTO `shop_key_value` VALUES ('14', 'term.taxonomy', '1caad667-985e-4b91-ef4a-fbbac872fbce', 'bind_form', 'article');
INSERT INTO `shop_key_value` VALUES ('15', 'term.taxonomy', '75d26c72-c68f-6c2b-3f5d-da6b85915a1c', 'page_num', '20');
INSERT INTO `shop_key_value` VALUES ('16', 'term.taxonomy', '75d26c72-c68f-6c2b-3f5d-da6b85915a1c', 'lists_tpl', 'news_list');
INSERT INTO `shop_key_value` VALUES ('17', 'term.taxonomy', '75d26c72-c68f-6c2b-3f5d-da6b85915a1c', 'detail_tpl', 'news_detail');
INSERT INTO `shop_key_value` VALUES ('18', 'term.taxonomy', '75d26c72-c68f-6c2b-3f5d-da6b85915a1c', 'bind_form', 'article');
INSERT INTO `shop_key_value` VALUES ('19', 'term.taxonomy', '8e830d6a-2be3-ad99-08b5-de279d877937', 'page_num', '20');
INSERT INTO `shop_key_value` VALUES ('20', 'term.taxonomy', '8e830d6a-2be3-ad99-08b5-de279d877937', 'lists_tpl', 'news_list');
INSERT INTO `shop_key_value` VALUES ('21', 'term.taxonomy', '8e830d6a-2be3-ad99-08b5-de279d877937', 'detail_tpl', 'news_detail');
INSERT INTO `shop_key_value` VALUES ('22', 'term.taxonomy', '8e830d6a-2be3-ad99-08b5-de279d877937', 'bind_form', 'article');
INSERT INTO `shop_key_value` VALUES ('29', 'posts.form', '085b628d-d8ae-d04c-dfa0-61992ca70f29', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('30', 'posts.form', '3cf4069c-80d0-ac82-fcfe-e7e378569c12', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('31', 'posts.form', '7df6d672-48ef-b8ed-1d18-74c3770dcbc3', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('32', 'posts.form', '7faa2c91-b173-6bd2-4b69-c0234c7c1a57', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('33', 'posts.form', 'b64c7e04-b8a0-eeda-0314-35eabe258111', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('34', 'posts.form', '8bc618f8-c8a4-2219-fee2-2da0a71ca8ff', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('35', 'posts.form', '8cfc3471-3754-30cb-b030-a11dba360e0c', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('36', 'posts.form', '9bb4e644-482b-c2cd-68c7-9a1a2f290435', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('37', 'posts.form', '1c6e5535-86e8-6e0b-548b-02e631b85b20', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('38', 'posts.form', '879bda21-07f8-df3c-9270-7789515157ed', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('39', 'posts.form', '74610495-ab86-d787-fa50-8ba3987b680b', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('40', 'posts.form', '76ce6961-894e-8d13-59c4-49881ddf6748', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('41', 'posts.form', '94714551-683d-aa79-6fb4-60dd70201473', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('42', 'posts.form', '11646a6e-cd35-bcdd-4136-c5b392b63a6f', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('43', 'posts.form', 'd27eea5e-e553-d2d5-b05b-9574af56ce3f', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('44', 'posts.form', '60e38eeb-97a5-61ac-be60-425f9f8eb1c5', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('45', 'posts.form', 'f569d8f0-0510-8c55-2cbf-f29a4ffea591', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('46', 'posts.form', 'e4ec7532-1686-71f3-f57e-e19cc49a81bf', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('47', 'posts.form', 'fabe0485-4f82-643a-6a46-cd8defc7f6d4', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('48', 'posts.form', '88de9d39-21e8-d00f-c8ff-2b56791ea559', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('49', 'users', '149f0e45-5cc0-dff6-d351-8e246c55b819', 'is_root', '1');
INSERT INTO `shop_key_value` VALUES ('50', 'posts.form', '0b2c8165-c99e-6daa-d8b0-e356b2e75ee8', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('51', 'posts.form', '87f478f4-b4b5-8429-c7fa-2ac816cc9276', 'description', '');
INSERT INTO `shop_key_value` VALUES ('52', 'posts.form', 'e13f3d7e-31dd-0356-3103-3d2e69258855', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('53', 'posts.form', '8a98549b-5e92-8595-8a75-941652b0cd44', 'description', '');
INSERT INTO `shop_key_value` VALUES ('54', 'posts.cover', '8a98549b-5e92-8595-8a75-941652b0cd44', 'cover_path_1', '/uploads/picture/20190220/489a269682d6d7378ad063e7bb755865.gif');
INSERT INTO `shop_key_value` VALUES ('55', 'posts.form', 'ddd32129-a296-10f4-410e-af801baecd26', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('56', 'posts.form', '7ab47541-c2dc-9e19-b8cf-98a97dc9467d', 'description', '');
INSERT INTO `shop_key_value` VALUES ('57', 'posts.form', '3ea9bf96-c5be-d853-1253-14b9846c8c97', 'page_tpl', 'page');
INSERT INTO `shop_key_value` VALUES ('59', 'posts.form', 'f40f4963-ecf9-fc58-b6a9-9fd7211b9bb5', 'description', '沙发');

-- ----------------------------
-- Table structure for shop_links
-- ----------------------------
DROP TABLE IF EXISTS `shop_links`;
CREATE TABLE `shop_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增唯一ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接URL',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '链接标题',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '链接图片',
  `target` varchar(25) NOT NULL DEFAULT '' COMMENT '链接打开方式',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '链接描述',
  `visible` varchar(20) NOT NULL DEFAULT 'Y' COMMENT '是否可见（Y/N）',
  `owner` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT '添加者用户ID',
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `link_visible` (`visible`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='连接表 路由需要';

-- ----------------------------
-- Records of shop_links
-- ----------------------------
INSERT INTO `shop_links` VALUES ('1', 'http://www.baidu.com', '百度一下', '', '_blank', '百度', 'Y', '1', '1474877272');

-- ----------------------------
-- Table structure for shop_menu
-- ----------------------------
DROP TABLE IF EXISTS `shop_menu`;
CREATE TABLE `shop_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COMMENT='菜单表';

-- ----------------------------
-- Records of shop_menu
-- ----------------------------
INSERT INTO `shop_menu` VALUES ('1', '文章', 'fa fa-fw fa-files-o', '0', '0', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('2', '订单', 'fa fa-fw fa-exchange', '0', '3', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('3', '会员', 'fa fa-fw fa-users', '0', '4', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('4', '设置', 'fa fa-gears', '0', '5', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('5', '个人', 'fa fa-fw fa-user', '0', '6', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('31', '写文章', 'fa fa-fw fa-edit', '1', '1', 'post/add', '0', '0');
INSERT INTO `shop_menu` VALUES ('32', '所有文章', 'fa fa-fw fa-file', '1', '0', 'post/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('37', '分类目录', 'fa fa-fw fa-cubes', '1', '2', 'taxonomy/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('38', '订单列表', 'fa fa-money', '2', '0', 'order/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('39', '会员列表', 'fa fa-fw fa-user', '3', '0', 'member/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('40', '添加会员', 'fa fa-fw fa-user-plus', '3', '1', 'member/add', '0', '0');
INSERT INTO `shop_menu` VALUES ('41', '基本设置', 'fa  fa-wrench', '4', '0', 'config/edit', '0', '0');
INSERT INTO `shop_menu` VALUES ('42', '菜单设置', 'fa  fa-navicon ', '4', '1', 'menu/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('43', '个人资料', 'fa fa-user-times', '5', '0', 'user/edit', '0', '0');
INSERT INTO `shop_menu` VALUES ('44', '修改密码', 'fa fa-fw fa-key', '5', '1', 'user/password', '0', '0');
INSERT INTO `shop_menu` VALUES ('50', '导航设置', 'fa  fa-cog', '4', '2', 'navigation/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('52', '所有页面', 'fa fa-fw fa-file', '51', '0', 'page/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('53', '新增页面', 'fa fa-edit (alias)', '51', '1', 'page/add', '0', '0');
INSERT INTO `shop_menu` VALUES ('54', '权限设置', 'fa fa-plug', '4', '0', 'authmanager/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('59', '登录', '', '0', '0', 'index/index', '1', '0');
INSERT INTO `shop_menu` VALUES ('60', '删除分类', '', '37', '0', 'taxonomyt/setStatus', '1', '0');
INSERT INTO `shop_menu` VALUES ('61', '添加分类目录', '', '37', '0', 'taxonomy/edit', '1', '0');
INSERT INTO `shop_menu` VALUES ('69', '数据库备份', 'fa fa-cog', '68', '0', 'Database/index?type=export', '0', '0');
INSERT INTO `shop_menu` VALUES ('70', '数据库还原', 'fa fa-cog', '68', '0', 'Database/index?type=import', '0', '0');
INSERT INTO `shop_menu` VALUES ('71', '商品', 'fa fa-shopping-cart', '0', '2', '#', '0', '0');
INSERT INTO `shop_menu` VALUES ('72', '所有商品', ' fa fa-shopping-cart', '71', '0', 'goods/index', '0', '0');
INSERT INTO `shop_menu` VALUES ('73', '添加商品', 'fa  fa-plus-square', '71', '1', 'goods/goodsAdd', '0', '0');
INSERT INTO `shop_menu` VALUES ('74', '商品分类', 'fa fa-list', '71', '2', 'goods/category', '0', '0');
INSERT INTO `shop_menu` VALUES ('75', '销售管理', 'fa fa-area-chart', '71', '3', 'goods/salesManagement', '0', '0');

-- ----------------------------
-- Table structure for shop_navigation
-- ----------------------------
DROP TABLE IF EXISTS `shop_navigation`;
CREATE TABLE `shop_navigation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='首页导航栏';

-- ----------------------------
-- Records of shop_navigation
-- ----------------------------
INSERT INTO `shop_navigation` VALUES ('1', '首页', 'fa fa-fw fa-files-o', '0', '0', 'index/index', '0');
INSERT INTO `shop_navigation` VALUES ('2', '关于我们', 'fa fa-fw fa-exchange', '0', '1', 'article/page?name=company', '0');
INSERT INTO `shop_navigation` VALUES ('3', '新闻资讯', 'fa fa-fw fa-users', '0', '2', 'article/lists?category=news', '0');
INSERT INTO `shop_navigation` VALUES ('4', '商品中心', 'fa fa-gears', '0', '3', 'goods/index', '0');
INSERT INTO `shop_navigation` VALUES ('5', '联系我们', 'fa fa-fw fa-edit', '0', '5', 'article/page?name=address', '0');

-- ----------------------------
-- Table structure for shop_orders
-- ----------------------------
DROP TABLE IF EXISTS `shop_orders`;
CREATE TABLE `shop_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `order_no` varchar(20) NOT NULL COMMENT '订单号',
  `print_no` varchar(30) DEFAULT NULL COMMENT '小票打印机单号',
  `express_type` varchar(100) DEFAULT NULL COMMENT '快递方式',
  `express_no` varchar(100) DEFAULT NULL COMMENT '快递编号',
  `pay_type` varchar(10) NOT NULL COMMENT '支付方式',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `createtime` int(11) NOT NULL,
  `is_pay` int(11) NOT NULL DEFAULT '0',
  `status` varchar(10) NOT NULL COMMENT '支付状态',
  `memo` varchar(255) DEFAULT NULL COMMENT '订单备注',
  `consignee_name` varchar(100) DEFAULT NULL COMMENT '收货人',
  `address` text COMMENT '收货地址',
  `mobile` varchar(11) DEFAULT NULL COMMENT '收货人电话',
  PRIMARY KEY (`id`,`uuid`,`order_no`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Records of shop_orders
-- ----------------------------
INSERT INTO `shop_orders` VALUES ('1', '57ef4ff9-cdb2-cc2e-40d1-dc3097aef23f', '7', '2019041450505697', null, '', '', 'wxpay', '1000.00', '1555204914', '0', 'completed', null, '', '', '');
INSERT INTO `shop_orders` VALUES ('2', '41135524-0071-4310-a00d-f6d5deaf73e5', '7', '2019041453505449', null, null, null, 'wxpay', '500.00', '1555205029', '0', 'paid', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('3', 'c15d6cf3-e989-e79d-5d14-c8d2c295cf2e', '7', '2019041455985010', null, null, null, 'wxpay', '2000.00', '1555205063', '0', 'paid', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('4', 'ce758812-4ae1-d2eb-e3c7-41ce84a5d2e5', '1', '2019041449999799', null, null, null, 'wxpay', '500.00', '1555207345', '0', 'delete', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('5', '1eaff118-4577-5cf2-d368-6936df04a5f1', '1', '2019041652514899', null, '', '', 'wxpay', '500.00', '1555418420', '0', 'delete', null, '', '', '');
INSERT INTO `shop_orders` VALUES ('6', '1b89c097-cac3-8f64-b9e6-0b22ee69a138', '1', '2019050310250525', null, null, null, 'wxpay', '2466264.00', '1556868239', '0', 'delete', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('7', '29fd3fe7-e539-9af2-97cc-ef1db9fb4ffc', '1', '2019050348515655', null, null, null, 'wxpay', '123.00', '1556868640', '0', 'shipped', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('8', '5d25be6f-1e6b-0eca-788b-1908febc902d', '1', '2019051498489710', null, null, null, 'wxpay', '3000.00', '1557849115', '0', 'shipped', null, null, '', null);
INSERT INTO `shop_orders` VALUES ('9', '0e83739c-ce33-68d8-580d-0b3bb8469747', '10', '2019051552995251', null, null, null, 'wxpay', '1000.00', '1557851316', '0', 'shipped', null, null, '', null);

-- ----------------------------
-- Table structure for shop_orders_address
-- ----------------------------
DROP TABLE IF EXISTS `shop_orders_address`;
CREATE TABLE `shop_orders_address` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `consignee_name` varchar(100) NOT NULL COMMENT '收货人',
  `province` varchar(100) NOT NULL COMMENT '省',
  `city` varchar(100) NOT NULL COMMENT '市',
  `county` varchar(100) NOT NULL COMMENT '县/区',
  `address` text NOT NULL COMMENT '详细地址',
  `mobile` varchar(11) NOT NULL COMMENT '联系电话',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '1-正常 -1-已删除',
  `default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为默认收货地址1-是 0-否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='订单收货地址';

-- ----------------------------
-- Records of shop_orders_address
-- ----------------------------
INSERT INTO `shop_orders_address` VALUES ('1', '3', '测试测试测试', '北京', '东城区', '', '测试测试测试测试', '17855834188', '1', '1');
INSERT INTO `shop_orders_address` VALUES ('2', '4', '测试测试', '北京', '东城区', '', '测试测试测试测试', '17855834187', '1', '1');
INSERT INTO `shop_orders_address` VALUES ('3', '5', 'qeqweq', '北京', '东城区', '', 'eqe', '17855845364', '1', '1');
INSERT INTO `shop_orders_address` VALUES ('4', '6', 'asdas', '北京', '东城区', '', 'dasd', '17855867433', '1', '1');

-- ----------------------------
-- Table structure for shop_orders_goods
-- ----------------------------
DROP TABLE IF EXISTS `shop_orders_goods`;
CREATE TABLE `shop_orders_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(11) NOT NULL COMMENT '订单号',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `name` varchar(255) NOT NULL,
  `num` int(10) NOT NULL COMMENT '购买数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text NOT NULL,
  `standard` varchar(255) NOT NULL,
  `cover_path` varchar(255) NOT NULL,
  `is_comment` varchar(10) NOT NULL DEFAULT '-1' COMMENT '商品是否评论 -1-否  1-是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='订单商品信息表';

-- ----------------------------
-- Records of shop_orders_goods
-- ----------------------------
INSERT INTO `shop_orders_goods` VALUES ('7', '1', '1', '耐克', '2', '500.00', '耐克', 'm50', '/uploads/picture/20190414/4f30622b26d35c96541337db2d619485.jpg', '1');
INSERT INTO `shop_orders_goods` VALUES ('8', '2', '1', '耐克', '1', '500.00', '耐克', 'm50', '/uploads/picture/20190414/4f30622b26d35c96541337db2d619485.jpg', '-1');
INSERT INTO `shop_orders_goods` VALUES ('9', '3', '1', '耐克', '4', '500.00', '耐克', 'm50', '/uploads/picture/20190414/4f30622b26d35c96541337db2d619485.jpg', '-1');
INSERT INTO `shop_orders_goods` VALUES ('10', '4', '1', '耐克', '1', '500.00', '耐克', 'm50', '/uploads/picture/20190414/4f30622b26d35c96541337db2d619485.jpg', '1');
INSERT INTO `shop_orders_goods` VALUES ('11', '5', '1', '耐克', '1', '500.00', '耐克', 'm50', '/uploads/picture/20190414/4f30622b26d35c96541337db2d619485.jpg', '-1');
INSERT INTO `shop_orders_goods` VALUES ('12', '6', '2', '1233', '2', '1233132.00', '4655', '12332', '/uploads/picture/20190503/3f6e46307aa23b7d34b5fe4aa7255795.PNG', '1');
INSERT INTO `shop_orders_goods` VALUES ('13', '7', '1', '23132', '1', '123.00', '3213212', '123', '/uploads/picture/20190503/64fd6e5c6c7df8bf9f19e60a8bfc1898.JPG', '-1');
INSERT INTO `shop_orders_goods` VALUES ('14', '8', '4', 'nake', '3', '1000.00', 'nake', 'M', '/uploads/picture/20190506/e7187b6feda1c523ad3b0a6584b09271.jpg', '-1');
INSERT INTO `shop_orders_goods` VALUES ('15', '9', '4', 'nake', '1', '1000.00', 'nake', 'M', '/uploads/picture/20190506/e7187b6feda1c523ad3b0a6584b09271.jpg', '-1');

-- ----------------------------
-- Table structure for shop_orders_status
-- ----------------------------
DROP TABLE IF EXISTS `shop_orders_status`;
CREATE TABLE `shop_orders_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL COMMENT '订单号',
  `approve_uid` int(50) DEFAULT NULL COMMENT '审核人',
  `trade_no` varchar(50) DEFAULT NULL COMMENT '支付接口流水号',
  `trade_status` varchar(50) DEFAULT NULL COMMENT '支付接口状态',
  `status` varchar(30) NOT NULL COMMENT 'nopaid-未支付 paid-已支付,待发货  shipped-已发货  completed-收货已完成',
  `createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='订单状态表';

-- ----------------------------
-- Records of shop_orders_status
-- ----------------------------
INSERT INTO `shop_orders_status` VALUES ('1', '2019021251504949', '1', null, null, 'shipped', '1549977997');
INSERT INTO `shop_orders_status` VALUES ('2', '2019022010153102', '1', null, null, 'shipped', '1550661340');
INSERT INTO `shop_orders_status` VALUES ('3', '2019022050515052', '1', null, null, 'shipped', '1550664891');
INSERT INTO `shop_orders_status` VALUES ('4', '2019041450505697', '1', null, null, 'shipped', '1555204966');
INSERT INTO `shop_orders_status` VALUES ('5', '2019041449999799', '1', null, null, 'shipped', '1555207439');
INSERT INTO `shop_orders_status` VALUES ('6', '2019041652514899', '1', null, null, 'shipped', '1556005099');
INSERT INTO `shop_orders_status` VALUES ('7', '2019050310250525', '1', null, null, 'shipped', '1556868296');
INSERT INTO `shop_orders_status` VALUES ('8', '2019050348515655', '1', null, null, 'shipped', '1556868661');
INSERT INTO `shop_orders_status` VALUES ('9', '2019051498489710', '1', null, null, 'shipped', '1557849147');
INSERT INTO `shop_orders_status` VALUES ('10', '2019051552995251', '1', null, null, 'shipped', '1557851330');

-- ----------------------------
-- Table structure for shop_posts
-- ----------------------------
DROP TABLE IF EXISTS `shop_posts`;
CREATE TABLE `shop_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增唯一ID',
  `uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '对应作者ID',
  `uuid` varchar(128) NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `content` longtext NOT NULL COMMENT '正文',
  `title` text NOT NULL COMMENT '标题',
  `description` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'publish' COMMENT '文章状态（publish/draft/inherit等）',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open' COMMENT '评论状态（open/closed）',
  `password` varchar(20) NOT NULL DEFAULT '' COMMENT '文章密码',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '文章缩略名',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父文章，主要用于PAGE',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `type` varchar(20) NOT NULL DEFAULT 'post' COMMENT '文章类型（post/page等）',
  `comment` bigint(20) NOT NULL DEFAULT '0' COMMENT '评论总数',
  `view` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览量',
  PRIMARY KEY (`id`),
  KEY `post_name` (`name`(191)),
  KEY `type_status_date` (`type`,`status`,`createtime`,`id`),
  KEY `post_parent` (`pid`),
  KEY `post_author` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='文章表';

-- ----------------------------
-- Records of shop_posts
-- ----------------------------
INSERT INTO `shop_posts` VALUES ('1', '1', '86a350ae-3b57-9084-aca5-85b40bcbfc2b', '1474852188', '<p>关于我们<br/></p>', '关于我们', '', 'publish', 'open', '', '', '1474852188', '0', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('2', '1', '3ea9bf96-c5be-d853-1253-14b9846c8c97', '1474852669', '<p>企业简介企业简介企业简介企业简介企业简介企业简介企业简介</p>', '企业简介', '', 'publish', 'open', '', 'company', '1550896886', '1', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('26', '1', 'f40f4963-ecf9-fc58-b6a9-9fd7211b9bb5', '1555204477', '<p>今天在学校写代码今天在学校写代码今天在学校写代码今天在学校写代码</p><audio controls=\"controls\" style=\"display: none;\"></audio>', '今天在学校写代码', '', 'publish', 'open', '', '', '1557159088', '0', '0', 'post', '0', '9');
INSERT INTO `shop_posts` VALUES ('25', '1', '7ab47541-c2dc-9e19-b8cf-98a97dc9467d', '1550664944', '<p>测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章测试文章</p>', '测试文章', '', 'trash', 'open', '', '', '1550664944', '0', '0', 'post', '0', '3');
INSERT INTO `shop_posts` VALUES ('3', '1', '1d3fa553-6e07-eed6-f459-4694de378122', '1474853044', '<p>企业文化</p>', '企业文化', '', 'publish', 'open', '', 'culture', '1474853044', '1', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('23', '1', '87f478f4-b4b5-8429-c7fa-2ac816cc9276', '1549889715', '<p>测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</p>', '测试测试测试测试测试测试', '', 'trash', 'open', '', '', '1549889715', '0', '0', 'post', '0', '3');
INSERT INTO `shop_posts` VALUES ('24', '1', '8a98549b-5e92-8595-8a75-941652b0cd44', '1550661196', '<p>测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333测试文章333</p>', '测试文章333', '', 'trash', 'open', '', '', '1550661196', '0', '0', 'post', '0', '1');
INSERT INTO `shop_posts` VALUES ('6', '1', '085b628d-d8ae-d04c-dfa0-61992ca70f29', '1474857641', '<p>发展历程</p>', '发展历程', '', 'publish', 'open', '', 'history', '1474857641', '1', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('7', '1', '7df6d672-48ef-b8ed-1d18-74c3770dcbc3', '1474857699', '<p>资质荣誉<br/></p>', '资质荣誉', '', 'publish', 'open', '', 'honor', '1474857719', '1', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('8', '1', '7faa2c91-b173-6bd2-4b69-c0234c7c1a57', '1474861254', '<p>联系我们</p>', '联系我们', '', 'publish', 'open', '', 'address', '1474861254', '0', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('9', '1', 'b64c7e04-b8a0-eeda-0314-35eabe258111', '1474875879', '<p>帮助中心<br/></p>', '帮助中心', '', 'publish', 'open', '', 'help', '1474875879', '0', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('10', '1', '9bb4e644-482b-c2cd-68c7-9a1a2f290435', '1474875914', '<p>购物指南</p>', '购物指南', '', 'publish', 'open', '', 'shopping', '1474875983', '9', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('11', '1', '8cfc3471-3754-30cb-b030-a11dba360e0c', '1474875963', '<p>账号注册</p>', '账号注册', '', 'publish', 'open', '', 'registration', '1474875963', '10', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('12', '1', '1c6e5535-86e8-6e0b-548b-02e631b85b20', '1474876064', '<p>购物流程</p>', '购物流程', '', 'publish', 'open', '', 'process', '1474876064', '10', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('13', '1', '879bda21-07f8-df3c-9270-7789515157ed', '1474876127', '<p>售后服务<br/></p>', '售后服务', '', 'publish', 'open', '', 'service', '1474876127', '9', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('14', '1', '74610495-ab86-d787-fa50-8ba3987b680b', '1474876180', '<p>先行赔付</p>', '先行赔付', '', 'publish', 'open', '', 'payment', '1474876180', '13', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('15', '1', '76ce6961-894e-8d13-59c4-49881ddf6748', '1474876216', '<p>退货流程</p>', '退货流程', '', 'publish', 'open', '', 'refund', '1474876216', '13', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('16', '1', '94714551-683d-aa79-6fb4-60dd70201473', '1474876249', '<p>投诉举报</p>', '投诉举报', '', 'publish', 'open', '', 'complain', '1474876249', '13', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('17', '1', '11646a6e-cd35-bcdd-4136-c5b392b63a6f', '1474876284', '<p>支付方式</p>', '支付方式', '', 'publish', 'open', '', 'payway', '1474876284', '9', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('18', '1', 'd27eea5e-e553-d2d5-b05b-9574af56ce3f', '1474876316', '<p>支付宝</p>', '支付宝', '', 'publish', 'open', '', 'alipay', '1474876316', '17', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('19', '1', 'f569d8f0-0510-8c55-2cbf-f29a4ffea591', '1474876350', '<p>微信支付</p>', '微信支付', '', 'publish', 'open', '', 'wxpay', '1474876382', '17', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('20', '1', 'e4ec7532-1686-71f3-f57e-e19cc49a81bf', '1474876431', '<p>配送方式<br/></p>', '配送方式', '', 'publish', 'open', '', 'distributionway', '1474876431', '9', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('21', '1', 'fabe0485-4f82-643a-6a46-cd8defc7f6d4', '1474876534', '<p>配送范围</p>', '配送范围', '', 'publish', 'open', '', 'distribution', '1474876534', '20', '0', 'page', '0', '0');
INSERT INTO `shop_posts` VALUES ('22', '1', '88de9d39-21e8-d00f-c8ff-2b56791ea559', '1474876595', '<p>运费计算</p>', '运费计算', '', 'publish', 'open', '', 'freight', '1474876595', '0', '0', 'page', '0', '0');

-- ----------------------------
-- Table structure for shop_terms
-- ----------------------------
DROP TABLE IF EXISTS `shop_terms`;
CREATE TABLE `shop_terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '分类名',
  `slug` varchar(200) NOT NULL DEFAULT '' COMMENT '缩略名',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='文章分类表';

-- ----------------------------
-- Records of shop_terms
-- ----------------------------
INSERT INTO `shop_terms` VALUES ('1', '新闻资讯', '', '0');
INSERT INTO `shop_terms` VALUES ('2', '企业新闻', 'news', '0');
INSERT INTO `shop_terms` VALUES ('3', '行业资讯', 'info', '0');

-- ----------------------------
-- Table structure for shop_term_relationships
-- ----------------------------
DROP TABLE IF EXISTS `shop_term_relationships`;
CREATE TABLE `shop_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '对应文章ID/链接ID',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '对应分类方法ID',
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章和分类关系表';

-- ----------------------------
-- Records of shop_term_relationships
-- ----------------------------
INSERT INTO `shop_term_relationships` VALUES ('24', '1', '0');
INSERT INTO `shop_term_relationships` VALUES ('23', '2', '0');
INSERT INTO `shop_term_relationships` VALUES ('24', '2', '0');
INSERT INTO `shop_term_relationships` VALUES ('25', '2', '0');
INSERT INTO `shop_term_relationships` VALUES ('26', '3', '0');

-- ----------------------------
-- Table structure for shop_term_taxonomy
-- ----------------------------
DROP TABLE IF EXISTS `shop_term_taxonomy`;
CREATE TABLE `shop_term_taxonomy` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类方法ID',
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分类方法(post_tag)',
  `uuid` varchar(128) NOT NULL,
  `taxonomy` varchar(32) NOT NULL DEFAULT '' COMMENT '分类方法(category)',
  `description` longtext NOT NULL,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '所属父分类方法ID',
  `count` bigint(20) NOT NULL DEFAULT '0' COMMENT '文章数统计',
  PRIMARY KEY (`id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='文章分类方法表';

-- ----------------------------
-- Records of shop_term_taxonomy
-- ----------------------------
INSERT INTO `shop_term_taxonomy` VALUES ('1', '1', '1caad667-985e-4b91-ef4a-fbbac872fbce', 'category', '', '0', '0');
INSERT INTO `shop_term_taxonomy` VALUES ('2', '2', '75d26c72-c68f-6c2b-3f5d-da6b85915a1c', 'category', '', '1', '0');
INSERT INTO `shop_term_taxonomy` VALUES ('3', '3', '8e830d6a-2be3-ad99-08b5-de279d877937', 'category', '', '1', '0');

-- ----------------------------
-- Table structure for shop_users
-- ----------------------------
DROP TABLE IF EXISTS `shop_users`;
CREATE TABLE `shop_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) NOT NULL COMMENT '系统唯一标识符',
  `username` varchar(60) DEFAULT NULL,
  `password` varchar(64) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(11) NOT NULL,
  `regdate` int(10) NOT NULL DEFAULT '0',
  `regip` char(15) NOT NULL DEFAULT '0',
  `salt` varchar(6) NOT NULL DEFAULT '0' COMMENT '加密盐',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1正常，2禁用，-1删除',
  `last_login` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `wechat_openid` varchar(255) DEFAULT NULL COMMENT '微信openid',
  `qq_openid` varchar(255) DEFAULT NULL COMMENT 'qqopenid',
  `sina_openid` varchar(255) NOT NULL COMMENT '微博openid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_users
-- ----------------------------
INSERT INTO `shop_users` VALUES ('1', '149f0e45-5cc0-dff6-d351-8e246c55b819', 'admin', 'c2c638349f6a9c702a0977774def5d38', 'admin', '123@qq.com', ' ', '1549889210', '::1', 'aeb454', '1', '1557849097', '', '', '');
INSERT INTO `shop_users` VALUES ('2', '9244d25d-2144-dd09-d4c9-b2f426a30e2c', 'qwe', 'c38b0abf41a1684f7204e024c20681c6', 'qwe', null, '17855834189', '1549938856', '0', '8be5d7', '1', '1549938863', null, null, '');
INSERT INTO `shop_users` VALUES ('3', 'fc28846d-e5a9-e49d-b7b0-59c9fae0e2f7', 'asd', 'f1dba4b9d4ebfc92aadc945a6f7be3a1', 'asd', null, '17855834188', '1549977392', '0', '0999b5', '1', '1550047169', null, null, '');
INSERT INTO `shop_users` VALUES ('4', '3aec2039-b697-8e13-d189-048a5c281303', 'zxc', '50c4cf41a6b9745eedea8637c4ccf6eb', 'zxczxc', null, '17855834187', '1549977876', '0', '4216e3', '1', '1549977884', null, null, '');
INSERT INTO `shop_users` VALUES ('5', '47db4897-39d4-ace6-5b93-60953eb2a5f8', 'asdasd', 'e02f5ec98b478e6b7381e08d50d38264', 'asdasd', null, '17855845364', '1550661278', '0', 'e38488', '1', '1550661284', null, null, '');
INSERT INTO `shop_users` VALUES ('6', '4d149fd3-b653-da12-e9d9-079d192a5cc4', 'zxczxc', 'a38dd90634df0536985e4feeb8937df4', 'zxczxc', null, '17855846456', '1550664835', '0', '37b0e9', '1', '1550664842', null, null, '');
INSERT INTO `shop_users` VALUES ('7', 'c87ba6d4-b4a1-26ae-77cf-871630bec261', '2015215215', 'a34d4995f34e8b45b88a428bca6aa0d9', 'cqaq', null, '', '1555203788', '0', 'cb0cea', '1', '1555203797', null, null, '');
INSERT INTO `shop_users` VALUES ('8', '40069e42-8003-d776-8fb4-684a905731ad', 'caohuwang', '874ee5ddbcd9e23d19b922feff428c36', 'ccc', '54646545', '1231654568', '0', '0', 'aaa98c', '1', null, null, null, '');
INSERT INTO `shop_users` VALUES ('9', '5ccce92f-4987-9d32-fce6-b1d7c5d4f91c', '123', 'f5ca5103a391d39e3b963fe7bb4a3c37', '123456', null, '18855056989', '1557115017', '0', '903196', '1', '1557115037', null, null, '');
INSERT INTO `shop_users` VALUES ('10', 'c5172880-df1b-1f1f-85ab-bfc52b636454', 'admin11', '2e22af0a4170146dd61f18e903ebd5f0', 'admin11', null, '11111111111', '1557851160', '0', '8c861d', '1', '1557851167', null, null, '');

-- ----------------------------
-- Table structure for shop_user_extend
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_extend`;
CREATE TABLE `shop_user_extend` (
  `group_id` mediumint(10) unsigned NOT NULL COMMENT '用户id',
  `extend_id` varchar(300) NOT NULL COMMENT '扩展表中数据的id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '扩展类型标识 1:栏目分类权限;2:模型权限',
  UNIQUE KEY `group_extend_type` (`group_id`,`extend_id`,`type`),
  KEY `uid` (`group_id`),
  KEY `group_id` (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组与分类的对应关系表';

-- ----------------------------
-- Records of shop_user_extend
-- ----------------------------

-- ----------------------------
-- Table structure for shop_user_group
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_group`;
CREATE TABLE `shop_user_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为-1禁用',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_user_group
-- ----------------------------
INSERT INTO `shop_user_group` VALUES ('1', 'admin', '1', '帆帆用户组', '', '1', '');

-- ----------------------------
-- Table structure for shop_user_group_access
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_group_access`;
CREATE TABLE `shop_user_group_access` (
  `uid` bigint(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_user_group_access
-- ----------------------------

-- ----------------------------
-- Table structure for shop_user_rule
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_rule`;
CREATE TABLE `shop_user_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_user_rule
-- ----------------------------
