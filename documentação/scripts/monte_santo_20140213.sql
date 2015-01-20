CREATE DATABASE  IF NOT EXISTS `monte_santo` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `monte_santo`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: 127.0.0.1    Database: monte_santo
-- ------------------------------------------------------
-- Server version	5.6.14

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `armazem`
--

DROP TABLE IF EXISTS `armazem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `armazem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `terminal_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_armazem_terminal1_idx` (`terminal_id`),
  CONSTRAINT `fk_armazem_terminal1` FOREIGN KEY (`terminal_id`) REFERENCES `terminal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `armazem`
--

LOCK TABLES `armazem` WRITE;
/*!40000 ALTER TABLE `armazem` DISABLE KEYS */;
/*!40000 ALTER TABLE `armazem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bancada`
--

DROP TABLE IF EXISTS `bancada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bancada` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fileira_x` varchar(45) NOT NULL,
  `pedreira_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bancada_pedreira1_idx` (`pedreira_id`),
  CONSTRAINT `fk_bancada_pedreira1` FOREIGN KEY (`pedreira_id`) REFERENCES `quarry` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancada`
--

LOCK TABLES `bancada` WRITE;
/*!40000 ALTER TABLE `bancada` DISABLE KEYS */;
/*!40000 ALTER TABLE `bancada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block`
--

DROP TABLE IF EXISTS `block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarry_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating_id` int(11) NOT NULL,
  `production_order_item_id` int(11) NOT NULL,
  `block_num` varchar(45) DEFAULT NULL,
  `tot_c` decimal(11,3) DEFAULT NULL,
  `tot_a` decimal(11,3) DEFAULT NULL,
  `tot_l` decimal(11,3) DEFAULT NULL,
  `tot_vol` decimal(11,3) DEFAULT NULL,
  `net_c` decimal(11,3) DEFAULT NULL,
  `net_a` decimal(11,3) DEFAULT NULL,
  `net_l` decimal(11,3) DEFAULT NULL,
  `net_vol` decimal(11,3) DEFAULT NULL,
  `obs` varchar(50) DEFAULT NULL,
  `reserved` tinyint(4) NOT NULL DEFAULT '0',
  `sold` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_bloco_pedreira1_idx` (`quarry_id`),
  KEY `fk_bloco_produto1_idx` (`product_id`),
  KEY `fk_bloco_classificacao1_idx` (`rating_id`),
  KEY `fk_bloco_item_producao1_idx` (`production_order_item_id`),
  CONSTRAINT `fk_bloco_classificacao1` FOREIGN KEY (`rating_id`) REFERENCES `rating` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_bloco_item_producao1` FOREIGN KEY (`production_order_item_id`) REFERENCES `production_order_item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_bloco_pedreira1` FOREIGN KEY (`quarry_id`) REFERENCES `quarry` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_bloco_produto1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block`
--

LOCK TABLES `block` WRITE;
/*!40000 ALTER TABLE `block` DISABLE KEYS */;
/*!40000 ALTER TABLE `block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bloco_foto`
--

DROP TABLE IF EXISTS `bloco_foto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bloco_foto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bloco_id` int(11) NOT NULL,
  `caminho` varchar(300) NOT NULL,
  `arquivo` varchar(300) NOT NULL,
  `tipo` varchar(300) NOT NULL,
  `tamanho` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bloco_foto_bloco1_idx` (`bloco_id`),
  CONSTRAINT `fk_bloco_foto_bloco1` FOREIGN KEY (`bloco_id`) REFERENCES `block` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloco_foto`
--

LOCK TABLES `bloco_foto` WRITE;
/*!40000 ALTER TABLE `bloco_foto` DISABLE KEYS */;
/*!40000 ALTER TABLE `bloco_foto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `name` varchar(150) NOT NULL,
  `code` varchar(10) NOT NULL,
  `doc_exig_com_inv` char(1) NOT NULL DEFAULT 'N',
  `doc_exig_pack_list` char(1) NOT NULL DEFAULT 'N',
  `doc_exig_bl` char(1) NOT NULL DEFAULT 'N',
  `doc_exig_certif_orig` char(1) NOT NULL DEFAULT 'N',
  `terms_of_payment` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_other` varchar(300) DEFAULT NULL,
  `eori` varchar(100) DEFAULT NULL,
  `consignee` varchar(500) DEFAULT NULL,
  `notify_address` varchar(500) DEFAULT NULL,
  `marks` varchar(50) DEFAULT NULL,
  `port_of_discharge` varchar(500) DEFAULT NULL,
  `port_of_delivery` varchar(500) DEFAULT NULL,
  `port_of_loading` varchar(500) DEFAULT NULL,
  `obs_body_of_bl` varchar(500) DEFAULT NULL,
  `desc_of_goods` varchar(500) DEFAULT NULL,
  `agencies` varchar(500) DEFAULT NULL,
  `obs` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'N','Kangli Xiamen','','N','N','N','N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'N','Best Cheer - Xiamen Shihu ae','','N','N','N','N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'N','Foshan Hong Kong','FHK','S','S','S','S','PAGAMENTO ANTECIPADO','Jay Chang','','+86 13760712669','','jay988@126.com','MSN: sohot988@hotmail.com','eori','TO ORDER','GUANGDONG YUNFU XINGYUN FOREIGN ECOMINIC & TRADE TEXTTILE CO.M, LTD\n3F NO 119 XINGYUN XI ROAD, YUNFU CITY, GUANGDONG, CHINA\nTEL: +85-766-8833303 FAX: +86-766-8810833','MS/BRASIL','HONG KONG / CHINA','','VITÓRIA (ES) PORT/BRAZIL','FREIGHT COLLECT\nCLEAN ON BOARD\nRE: 12/06255-8','POS VITORIA PORT (ES), BRAZIL\nROUSH BLOCKS\nDESCRIPTION                                    QUANTITY\nGIALLO CALIFORNIA II EXTRA          33.560M3\nGIALLO CALIFORNIA F II EXTRA       12.443M3','Adriana Miranda \nOrient Granite Express Fretes Ltda \nPhone/Fax: +55 027 3233-3120 \nMobile        : +55 027 9249-0070 \nE-mail: adriana@orientgranite.com.br \n           orientgranite@orientgranite.com.br \n\nWilson Freitas\nOperation Department\nDirect: +55 27 21241656\nPhone : +55 27 21241654\nMobile: +55 27 99634405\nNEXTEL: 55*91*111144\nFax   : +55 27 21241655\ne-mail: agency@transhipping.com.br\nMSN   : wilsinho2k@hotmail.com\nSkype : wgf.wilson\n','ESSE CLIENTE AS VEZES TROCA DE EMPRESA IMPORTADORA:\nFOSHAN ESNO INTERNATIONAL TRADING CO., LTD.'),(4,'N','Free True Hong Kong','','N','N','N','N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'N','Fuying - Xiamen','','N','N','N','N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'S','thiago bighetti','tbb','N','S','N','N','','','','','','','','','','','','','','','','','',''),(7,'N','THIAGO BIGHETTI','TBB','N','N','N','N','termos','contato','telefone','mobile','fax','email@bighetti.com','msn.. skype..','eori','consignee','notify','mark','discharge','delivery','loading','bl','goods','agencies','obs');
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `defect`
--

DROP TABLE IF EXISTS `defect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `defect`
--

LOCK TABLES `defect` WRITE;
/*!40000 ALTER TABLE `defect` DISABLE KEYS */;
INSERT INTO `defect` VALUES (1,'N','M.CR','Mancha de cristal'),(2,'N','V.AMAR','Veio Amarelo'),(3,'S','TESTE DEFEITO',''),(4,'S','teste','123'),(5,'N','2 COR ','Duas cores'),(6,'N','V. CR','Veio de cristal'),(7,'N','V. CR (Fino)','Veio de Cristal Fino'),(8,'N','V. ESCURO','Veio escuro'),(9,'N','V. GRANF','?'),(10,'N','F. PR','Fio Preto'),(11,'N','M. VM','Mancha vermelha'),(12,'N','V. VM','Veio vermelho'),(13,'N','V. BR','Veio Branco'),(14,'N','V. VDE','Veio Verde');
/*!40000 ALTER TABLE `defect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `defect_poi`
--

DROP TABLE IF EXISTS `defect_poi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defect_poi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_order_item_id` int(11) NOT NULL,
  `defect_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_defect_poi_defect1_idx` (`defect_id`),
  KEY `fk_defect_poi_production_order_item1_idx` (`production_order_item_id`),
  CONSTRAINT `fk_defect_poi_defect1` FOREIGN KEY (`defect_id`) REFERENCES `defect` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_defect_poi_production_order_item1` FOREIGN KEY (`production_order_item_id`) REFERENCES `production_order_item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `defect_poi`
--

LOCK TABLES `defect_poi` WRITE;
/*!40000 ALTER TABLE `defect_poi` DISABLE KEYS */;
INSERT INTO `defect_poi` VALUES (7,3,1),(8,3,2),(9,3,3),(10,2,5),(11,2,6),(12,2,7);
/*!40000 ALTER TABLE `defect_poi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque_armazem`
--

DROP TABLE IF EXISTS `estoque_armazem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estoque_armazem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `armazem_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_estoque_armazem_armazem1_idx` (`armazem_id`),
  KEY `fk_estoque_armazem_produto1_idx` (`produto_id`),
  CONSTRAINT `fk_estoque_armazem_armazem1` FOREIGN KEY (`armazem_id`) REFERENCES `armazem` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_estoque_armazem_produto1` FOREIGN KEY (`produto_id`) REFERENCES `product` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque_armazem`
--

LOCK TABLES `estoque_armazem` WRITE;
/*!40000 ALTER TABLE `estoque_armazem` DISABLE KEYS */;
/*!40000 ALTER TABLE `estoque_armazem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `front`
--

DROP TABLE IF EXISTS `front`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarry_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `fk_frente_pedreira1_idx` (`quarry_id`),
  CONSTRAINT `fk_frente_pedreira1` FOREIGN KEY (`quarry_id`) REFERENCES `quarry` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `front`
--

LOCK TABLES `front` WRITE;
/*!40000 ALTER TABLE `front` DISABLE KEYS */;
INSERT INTO `front` VALUES (1,1,'Frente 1','N'),(2,1,'Frente 2','S'),(3,2,'Frente 1b','S'),(4,2,'Frente 2','N'),(5,2,'Frente 3','N');
/*!40000 ALTER TABLE `front` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspecao_cliente_agenda`
--

DROP TABLE IF EXISTS `inspecao_cliente_agenda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspecao_cliente_agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `item_producao_id` int(11) NOT NULL,
  `dia` date NOT NULL,
  `hora` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_inspecao_cliente_cliente1_idx` (`cliente_id`),
  KEY `fk_inspecao_cliente_item_producao1_idx` (`item_producao_id`),
  CONSTRAINT `fk_inspecao_cliente_cliente1` FOREIGN KEY (`cliente_id`) REFERENCES `client` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_inspecao_cliente_item_producao1` FOREIGN KEY (`item_producao_id`) REFERENCES `production_order_item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspecao_cliente_agenda`
--

LOCK TABLES `inspecao_cliente_agenda` WRITE;
/*!40000 ALTER TABLE `inspecao_cliente_agenda` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspecao_cliente_agenda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local`
--

DROP TABLE IF EXISTS `local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `local` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `tipo` int(11) NOT NULL COMMENT '1 = Pedreira\n2 = Cidade\n3 = Porto\n4 = Enviado',
  `pedreira_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_local_pedreira1_idx` (`pedreira_id`),
  CONSTRAINT `fk_local_pedreira1` FOREIGN KEY (`pedreira_id`) REFERENCES `quarry` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local`
--

LOCK TABLES `local` WRITE;
/*!40000 ALTER TABLE `local` DISABLE KEYS */;
/*!40000 ALTER TABLE `local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_venda`
--

DROP TABLE IF EXISTS `pedido_venda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_venda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `inspecao_cliente_agenda_id` int(11) DEFAULT NULL,
  `data_registro` datetime DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `nota_fiscal` varchar(9) DEFAULT NULL,
  `valor` decimal(11,2) DEFAULT NULL,
  `c` decimal(11,3) DEFAULT NULL,
  `a` decimal(11,3) DEFAULT NULL,
  `b` decimal(11,3) DEFAULT NULL,
  `volume` decimal(11,3) DEFAULT NULL,
  `peso` decimal(11,3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pedido_venda_cliente1_idx` (`cliente_id`),
  KEY `fk_pedido_venda_inspecao_cliente_agenda1_idx` (`inspecao_cliente_agenda_id`),
  CONSTRAINT `fk_pedido_venda_cliente1` FOREIGN KEY (`cliente_id`) REFERENCES `client` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pedido_venda_inspecao_cliente_agenda1` FOREIGN KEY (`inspecao_cliente_agenda_id`) REFERENCES `inspecao_cliente_agenda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_venda`
--

LOCK TABLES `pedido_venda` WRITE;
/*!40000 ALTER TABLE `pedido_venda` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedido_venda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_venda_item`
--

DROP TABLE IF EXISTS `pedido_venda_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_venda_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_venda_id` int(11) NOT NULL,
  `bloco_id` int(11) NOT NULL,
  `tot_c` decimal(11,3) NOT NULL,
  `tot_a` decimal(11,3) NOT NULL,
  `tot_l` decimal(11,3) NOT NULL,
  `tot_vol` decimal(11,3) NOT NULL,
  `net_c` decimal(11,3) NOT NULL,
  `net_a` decimal(11,3) NOT NULL,
  `net_l` decimal(11,3) NOT NULL,
  `net_vol` decimal(11,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pedido_venda_item_pedido_venda1_idx` (`pedido_venda_id`),
  KEY `fk_pedido_venda_item_bloco1_idx` (`bloco_id`),
  CONSTRAINT `fk_pedido_venda_item_bloco1` FOREIGN KEY (`bloco_id`) REFERENCES `block` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pedido_venda_item_pedido_venda1` FOREIGN KEY (`pedido_venda_id`) REFERENCES `pedido_venda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_venda_item`
--

LOCK TABLES `pedido_venda_item` WRITE;
/*!40000 ALTER TABLE `pedido_venda_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedido_venda_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,'N','Gialo'),(2,'S','Produto teste');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_order`
--

DROP TABLE IF EXISTS `production_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `production_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `front_id` int(11) NOT NULL,
  `date_production` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_ordem_producao_frente1_idx` (`front_id`),
  CONSTRAINT `fk_ordem_producao_frente1` FOREIGN KEY (`front_id`) REFERENCES `front` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_order`
--

LOCK TABLES `production_order` WRITE;
/*!40000 ALTER TABLE `production_order` DISABLE KEYS */;
INSERT INTO `production_order` VALUES (1,'N',1,'2013-02-04',0),(2,'N',1,'2013-02-05',0),(3,'N',1,'2014-02-11',0),(4,'S',5,'2014-02-09',0),(5,'N',1,'2014-02-08',0),(6,'S',4,'2014-02-07',0),(7,'N',4,'2014-02-06',0),(8,'N',4,'2014-02-10',0),(9,'N',4,'2014-02-10',0),(10,'N',4,'2014-02-10',0),(11,'N',4,'2014-02-12',0);
/*!40000 ALTER TABLE `production_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_order_item`
--

DROP TABLE IF EXISTS `production_order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `production_order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `product_id` int(11) NOT NULL,
  `rating_id` int(11) NOT NULL,
  `production_order_id` int(11) NOT NULL,
  `block_num` varchar(45) NOT NULL,
  `tot_c` decimal(11,3) NOT NULL,
  `tot_a` decimal(11,3) NOT NULL,
  `tot_l` decimal(11,3) NOT NULL,
  `tot_vol` decimal(11,3) NOT NULL,
  `net_c` decimal(11,3) DEFAULT NULL,
  `net_a` decimal(11,3) DEFAULT NULL,
  `net_l` decimal(11,3) DEFAULT NULL,
  `net_vol` decimal(11,3) DEFAULT NULL,
  `obs` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item_produto1_idx` (`product_id`),
  KEY `fk_item_producao_classificacao1_idx` (`rating_id`),
  KEY `fk_item_producao_ordem_producao1_idx` (`production_order_id`),
  CONSTRAINT `fk_item_producao_classificacao1` FOREIGN KEY (`rating_id`) REFERENCES `rating` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_producao_ordem_producao1` FOREIGN KEY (`production_order_id`) REFERENCES `production_order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_produto1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_order_item`
--

LOCK TABLES `production_order_item` WRITE;
/*!40000 ALTER TABLE `production_order_item` DISABLE KEYS */;
INSERT INTO `production_order_item` VALUES (2,'N',1,1,11,'HSHDHYSI',3.000,2.000,4.000,24.000,2.000,1.000,3.000,6.000,'TESTE'),(3,'N',1,1,11,'ISJSHBU',3.424,2.345,4.123,5.778,3.245,2.435,4.345,2.345,'teste 2');
/*!40000 ALTER TABLE `production_order_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quarry`
--

DROP TABLE IF EXISTS `quarry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quarry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quarry`
--

LOCK TABLES `quarry` WRITE;
/*!40000 ALTER TABLE `quarry` DISABLE KEYS */;
INSERT INTO `quarry` VALUES (1,'N','Bauru'),(2,'N','Agudos'),(3,'S','Piratininga teste'),(4,'S','teste');
/*!40000 ALTER TABLE `quarry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rating`
--

DROP TABLE IF EXISTS `rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `excluido` char(1) NOT NULL DEFAULT 'N',
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rating`
--

LOCK TABLES `rating` WRITE;
/*!40000 ALTER TABLE `rating` DISABLE KEYS */;
INSERT INTO `rating` VALUES (1,'N','II EX'),(2,'N','I EX');
/*!40000 ALTER TABLE `rating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reserva_cliente`
--

DROP TABLE IF EXISTS `reserva_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reserva_cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `bloco_id` int(11) NOT NULL,
  `data_registro` datetime DEFAULT NULL,
  `q` varchar(45) DEFAULT NULL,
  `mc` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reserva_cliente1_idx` (`cliente_id`),
  KEY `fk_reserva_cliente_bloco1_idx` (`bloco_id`),
  CONSTRAINT `fk_reserva_cliente_bloco1` FOREIGN KEY (`bloco_id`) REFERENCES `block` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_reserva_cliente_cliente1` FOREIGN KEY (`cliente_id`) REFERENCES `client` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva_cliente`
--

LOCK TABLES `reserva_cliente` WRITE;
/*!40000 ALTER TABLE `reserva_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserva_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terminal`
--

DROP TABLE IF EXISTS `terminal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terminal` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terminal`
--

LOCK TABLES `terminal` WRITE;
/*!40000 ALTER TABLE `terminal` DISABLE KEYS */;
/*!40000 ALTER TABLE `terminal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tricante`
--

DROP TABLE IF EXISTS `tricante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tricante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tricante`
--

LOCK TABLES `tricante` WRITE;
/*!40000 ALTER TABLE `tricante` DISABLE KEYS */;
/*!40000 ALTER TABLE `tricante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veiculo`
--

DROP TABLE IF EXISTS `veiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `veiculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `identificacao` varchar(50) DEFAULT NULL COMMENT 'Ex: placa do caminhão',
  `capacidade` decimal(11,3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veiculo`
--

LOCK TABLES `veiculo` WRITE;
/*!40000 ALTER TABLE `veiculo` DISABLE KEYS */;
/*!40000 ALTER TABLE `veiculo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-02-13 15:11:54
