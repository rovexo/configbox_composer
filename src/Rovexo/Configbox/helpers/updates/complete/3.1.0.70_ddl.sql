SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `sltxh_cbcheckout_order_cities`
(
    `id`        int(10) unsigned NOT NULL,
    `order_id`  int(10) unsigned NOT NULL DEFAULT '0',
    `city_name` varchar(200)     NOT NULL DEFAULT '',
    `county_id` int(10) unsigned          DEFAULT NULL,
    `custom_1`  text             NOT NULL,
    `custom_2`  text             NOT NULL,
    `custom_3`  text             NOT NULL,
    `custom_4`  text             NOT NULL,
    `ordering`  int(11)                   DEFAULT NULL,
    `published` varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`, `order_id`),
    KEY `state_id` (`county_id`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_cities_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE `sltxh_cbcheckout_order_configurations`
(
    `id`                        int(10) unsigned                      NOT NULL AUTO_INCREMENT,
    `position_id`               int(10) unsigned                               DEFAULT NULL,
    `price_net`                 decimal(20, 4)                        NOT NULL DEFAULT '0.0000',
    `price_overrides`           varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
    `price_recurring_net`       decimal(20, 4)                        NOT NULL DEFAULT '0.0000',
    `price_recurring_overrides` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
    `element_id`                int(10) unsigned                               DEFAULT NULL,
    `element_type`              varchar(30) COLLATE utf8_unicode_ci   NOT NULL,
    `weight`                    decimal(20, 3)                        NOT NULL DEFAULT '0.000',
    `xref_id`                   int(10) unsigned                               DEFAULT NULL,
    `option_id`                 int(10) unsigned                               DEFAULT NULL,
    `value`                     text COLLATE utf8_unicode_ci          NOT NULL,
    `output_value`              text COLLATE utf8_unicode_ci          NOT NULL,
    `element_code`              varchar(255) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
    `option_sku`                varchar(255) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
    `option_image`              varchar(50) COLLATE utf8_unicode_ci   NOT NULL DEFAULT '',
    `show_in_overviews`         int(10) unsigned                               DEFAULT '1',
    `element_custom_1`          text COLLATE utf8_unicode_ci          NOT NULL,
    `element_custom_2`          text COLLATE utf8_unicode_ci          NOT NULL,
    `element_custom_3`          text COLLATE utf8_unicode_ci          NOT NULL,
    `element_custom_4`          text COLLATE utf8_unicode_ci          NOT NULL,
    `assignment_custom_1`       text COLLATE utf8_unicode_ci          NOT NULL,
    `assignment_custom_2`       text COLLATE utf8_unicode_ci          NOT NULL,
    `assignment_custom_3`       text COLLATE utf8_unicode_ci          NOT NULL,
    `assignment_custom_4`       text COLLATE utf8_unicode_ci          NOT NULL,
    `option_custom_1`           text COLLATE utf8_unicode_ci          NOT NULL,
    `option_custom_2`           text COLLATE utf8_unicode_ci          NOT NULL,
    `option_custom_3`           text COLLATE utf8_unicode_ci          NOT NULL,
    `option_custom_4`           text COLLATE utf8_unicode_ci          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `element_id` (`element_id`),
    KEY `xref_id` (`xref_id`),
    KEY `position_id` (`position_id`),
    CONSTRAINT `sltxh_cbcheckout_order_configurations_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `sltxh_cbcheckout_order_positions` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_counties`
(
    `id`          int(10) unsigned NOT NULL,
    `order_id`    int(10) unsigned NOT NULL DEFAULT '0',
    `county_name` varchar(200)     NOT NULL DEFAULT '',
    `state_id`    int(10) unsigned          DEFAULT NULL,
    `custom_1`    text             NOT NULL,
    `custom_2`    text             NOT NULL,
    `custom_3`    text             NOT NULL,
    `custom_4`    text             NOT NULL,
    `ordering`    int(11)                   DEFAULT NULL,
    `published`   varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`, `order_id`),
    KEY `state_id` (`state_id`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_counties_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_countries`
(
    `id`             int(10) unsigned NOT NULL,
    `order_id`       int(10) unsigned NOT NULL DEFAULT '0',
    `country_name`   varchar(64)               DEFAULT NULL,
    `country_3_code` char(3)                   DEFAULT NULL,
    `country_2_code` char(2)                   DEFAULT NULL,
    `vat_free`       int(11)                   DEFAULT '1',
    `in_eu_vat_area` varchar(1)       NOT NULL DEFAULT '0',
    `published`      int(11)                   DEFAULT '1',
    `ordering`       int(11)                   DEFAULT NULL,
    `custom_1`       text             NOT NULL,
    `custom_2`       text             NOT NULL,
    `custom_3`       text             NOT NULL,
    `custom_4`       text             NOT NULL,
    PRIMARY KEY (`id`, `order_id`),
    KEY `idx_country_name` (`country_name`),
    KEY `order_id` (`order_id`),
    KEY `in_eu_vat_area` (`in_eu_vat_area`),
    CONSTRAINT `sltxh_cbcheckout_order_countries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_currencies`
(
    `id`            int(10) unsigned        NOT NULL,
    `order_id`      int(10) unsigned        NOT NULL DEFAULT '0',
    `base`          int(11)                          DEFAULT '0',
    `multiplicator` decimal(10, 5) unsigned NOT NULL,
    `symbol`        varchar(10)             NOT NULL DEFAULT '',
    `code`          varchar(10)             NOT NULL DEFAULT '',
    `default`       int(11)                          DEFAULT '0',
    `ordering`      int(11)                          DEFAULT '0',
    `published`     int(11)                          DEFAULT '1',
    PRIMARY KEY (`id`, `order_id`),
    KEY `code` (`code`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_currencies_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_invoices`
(
    `id`                    int(11)                              NOT NULL AUTO_INCREMENT,
    `invoice_number_prefix` varchar(10) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
    `invoice_number_serial` int(10) unsigned                     NOT NULL,
    `order_id`              int(10) unsigned                              DEFAULT NULL,
    `file`                  varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `released_by`           int(11)                              NOT NULL DEFAULT '0',
    `released_on`           datetime                             NOT NULL COMMENT 'UTC Timing',
    `changed`               varchar(1) COLLATE utf8_unicode_ci   NOT NULL DEFAULT '0',
    `original_file`         varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `changed_by`            int(10) unsigned                     NOT NULL DEFAULT '0',
    `changed_on`            datetime                                      DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_prefix_serial` (`invoice_number_prefix`, `invoice_number_serial`),
    UNIQUE KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_payment_methods`
(
    `order_id`       int(10) unsigned NOT NULL DEFAULT '0',
    `id`             int(10) unsigned NOT NULL DEFAULT '0',
    `price`          decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `taxclass_id`    int(10) unsigned          DEFAULT '0',
    `params`         varchar(255)     NOT NULL DEFAULT '',
    `ordering`       int(11)                   DEFAULT '0',
    `percentage`     decimal(20, 3)   NOT NULL DEFAULT '0.000',
    `price_min`      decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `price_max`      decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `connector_name` varchar(50)      NOT NULL DEFAULT '',
    PRIMARY KEY (`order_id`, `id`),
    KEY `index_price` (`price`),
    KEY `index_price_max` (`price_max`),
    KEY `index_price_min` (`price_min`),
    CONSTRAINT `sltxh_cbcheckout_order_payment_methods_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_payment_trackings`
(
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`     int(10) unsigned NOT NULL,
    `order_id`    int(10) unsigned          DEFAULT NULL,
    `got_tracked` varchar(1)       NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`, `order_id`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_payment_trackings_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_positions`
(
    `id`                                     int(10) unsigned                      NOT NULL AUTO_INCREMENT,
    `order_id`                               int(10) unsigned                               DEFAULT NULL,
    `product_id`                             int(10) unsigned                               DEFAULT '0',
    `product_sku`                            varchar(255) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
    `product_image`                          varchar(50) COLLATE utf8_unicode_ci   NOT NULL DEFAULT '',
    `quantity`                               int(10) unsigned                               DEFAULT '1',
    `weight`                                 decimal(20, 3) unsigned               NOT NULL DEFAULT '0.000',
    `taxclass_id`                            int(10) unsigned                               DEFAULT '0',
    `taxclass_recurring_id`                  int(10) unsigned                               DEFAULT '0',
    `product_base_price_net`                 decimal(20, 4) unsigned               NOT NULL DEFAULT '0.0000',
    `product_base_price_overrides`           varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
    `product_base_price_recurring_net`       decimal(20, 4) unsigned               NOT NULL DEFAULT '0.0000',
    `product_base_price_recurring_overrides` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
    `price_net`                              decimal(20, 4) unsigned               NOT NULL DEFAULT '0.0000',
    `price_recurring_net`                    decimal(20, 4) unsigned               NOT NULL DEFAULT '0.0000',
    `open_amount_net`                        decimal(20, 4) unsigned               NOT NULL DEFAULT '0.0000',
    `using_deposit`                          int(10) unsigned                               DEFAULT '0',
    `dispatch_time`                          int(10) unsigned                               DEFAULT '0',
    `product_custom_1`                       text COLLATE utf8_unicode_ci          NOT NULL,
    `product_custom_2`                       text COLLATE utf8_unicode_ci          NOT NULL,
    `product_custom_3`                       text COLLATE utf8_unicode_ci          NOT NULL,
    `product_custom_4`                       text COLLATE utf8_unicode_ci          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `sltxh_cbcheckout_order_positions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_quotations`
(
    `order_id`   int(10) unsigned                    NOT NULL AUTO_INCREMENT,
    `created_on` datetime                                     DEFAULT NULL COMMENT 'UTC',
    `created_by` int(10) unsigned                    NOT NULL DEFAULT '0',
    `file`       varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_quotations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_records`
(
    `id`                  int(10) unsigned                   NOT NULL AUTO_INCREMENT,
    `user_id`             int(10) unsigned                            DEFAULT NULL,
    `delivery_id`         int(10) unsigned                            DEFAULT NULL,
    `payment_id`          int(10) unsigned                            DEFAULT NULL,
    `cart_id`             int(10) unsigned                            DEFAULT NULL,
    `store_id`            int(10) unsigned                   NOT NULL DEFAULT '0',
    `created_on`          datetime                                    DEFAULT NULL COMMENT 'UTC',
    `paid`                int(11)                                     DEFAULT '0',
    `paid_on`             datetime                                    DEFAULT NULL,
    `status`              int(10) unsigned                            DEFAULT '0',
    `invoice_released`    varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
    `comment`             text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_1`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_2`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_3`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_4`            text COLLATE utf8_unicode_ci       NOT NULL,
    `coupon_discount_net` decimal(20, 4)                     NOT NULL DEFAULT '0.0000',
    `transaction_id`      text COLLATE utf8_unicode_ci       NOT NULL,
    `transaction_data`    text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_5`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_6`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_7`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_8`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_9`            text COLLATE utf8_unicode_ci       NOT NULL,
    `custom_10`           text COLLATE utf8_unicode_ci       NOT NULL,
    `ga_client_id`        varchar(255) COLLATE utf8_unicode_ci        DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `cart_id` (`cart_id`),
    CONSTRAINT `sltxh_cbcheckout_order_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sltxh_cbcheckout_order_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_salutations`
(
    `id`       int(10) unsigned NOT NULL,
    `order_id` int(10) unsigned NOT NULL DEFAULT '0',
    `gender`   varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`, `order_id`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_salutations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_shipping_methods`
(
    `order_id`     int(10) unsigned        NOT NULL DEFAULT '0',
    `id`           int(10) unsigned        NOT NULL DEFAULT '0',
    `shipper_id`   int(10) unsigned                 DEFAULT NULL,
    `zone_id`      int(10) unsigned                 DEFAULT NULL,
    `minweight`    decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `maxweight`    decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `deliverytime` int(11)                          DEFAULT NULL,
    `price`        decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `taxclass_id`  int(10) unsigned                 DEFAULT NULL,
    `external_id`  varchar(100)            NOT NULL DEFAULT '',
    `ordering`     int(11)                          DEFAULT '0',
    PRIMARY KEY (`order_id`, `id`),
    KEY `minweight` (`minweight`),
    KEY `maxweight` (`maxweight`),
    KEY `price` (`price`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_cbcheckout_order_shipping_methods_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_states`
(
    `id`          int(10) unsigned NOT NULL,
    `order_id`    int(10) unsigned NOT NULL DEFAULT '0',
    `country_id`  int(10) unsigned          DEFAULT NULL,
    `name`        varchar(50)      NOT NULL DEFAULT '',
    `iso_code`    varchar(50)      NOT NULL DEFAULT '',
    `fips_number` varchar(5)       NOT NULL DEFAULT '',
    `custom_1`    text             NOT NULL,
    `custom_2`    text             NOT NULL,
    `custom_3`    text             NOT NULL,
    `custom_4`    text             NOT NULL,
    `ordering`    int(11)                   DEFAULT '0',
    `published`   int(10) unsigned          DEFAULT '1',
    PRIMARY KEY (`id`, `order_id`),
    KEY `country_id` (`country_id`),
    KEY `ordering` (`ordering`, `published`),
    KEY `iso_fips` (`iso_code`, `fips_number`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_states_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_strings`
(
    `order_id`     int(10) unsigned                                       NOT NULL DEFAULT '0',
    `table`        varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `type`         int(5) unsigned                                        NOT NULL COMMENT '1:shippers',
    `key`          bigint(20) unsigned                                    NOT NULL,
    `language_tag` char(5)                                                NOT NULL DEFAULT '',
    `text`         text                                                   NOT NULL,
    PRIMARY KEY (`order_id`, `table`, `type`, `key`, `language_tag`),
    CONSTRAINT `sltxh_cbcheckout_order_strings_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_tax_class_rates`
(
    `order_id`         int(10) unsigned                 DEFAULT NULL,
    `tax_class_id`     int(10) unsigned                 DEFAULT NULL,
    `city_id`          int(10) unsigned        NOT NULL DEFAULT '0',
    `county_id`        int(10) unsigned        NOT NULL DEFAULT '0',
    `zone_id`          int(10) unsigned                 DEFAULT NULL,
    `state_id`         int(10) unsigned                 DEFAULT NULL,
    `country_id`       int(10) unsigned        NOT NULL,
    `tax_rate`         decimal(4, 2) unsigned  NOT NULL,
    `default_tax_rate` decimal(10, 3) unsigned NOT NULL,
    `tax_code`         varchar(100)            NOT NULL DEFAULT '',
    UNIQUE KEY `unique_all` (`order_id`, `tax_class_id`, `city_id`, `county_id`, `state_id`, `country_id`),
    CONSTRAINT `sltxh_cbcheckout_order_tax_class_rates_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_cbcheckout_order_user_groups`
(
    `order_id`                    int(10) unsigned                     NOT NULL DEFAULT '0',
    `group_id`                    int(10) unsigned                     NOT NULL DEFAULT '0',
    `discount_start_1`            decimal(20, 4) unsigned              NOT NULL DEFAULT '0.0000',
    `discount_start_2`            decimal(20, 4) unsigned              NOT NULL DEFAULT '0.0000',
    `discount_start_3`            decimal(20, 4) unsigned              NOT NULL DEFAULT '0.0000',
    `discount_start_4`            decimal(20, 4) unsigned              NOT NULL DEFAULT '0.0000',
    `discount_start_5`            decimal(20, 4) unsigned              NOT NULL DEFAULT '0.0000',
    `discount_factor_1`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_factor_2`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_factor_3`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_factor_4`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_factor_5`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_amount_1`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_amount_2`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_amount_3`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_amount_4`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_amount_5`           decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_type_1`             varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_type_2`             varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_type_3`             varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_type_4`             varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_type_5`             varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_amount_1` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_2` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_3` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_4` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_5` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_type_1`   varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_2`   varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_3`   varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_4`   varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_5`   varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT 'percentage',
    `discount_recurring_start_1`  decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_2`  decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_3`  decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_4`  decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_5`  decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_1` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_2` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_3` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_4` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_5` decimal(20, 4)                       NOT NULL DEFAULT '0.0000',
    `title`                       varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `custom_1`                    text COLLATE utf8_unicode_ci         NOT NULL,
    `custom_2`                    text COLLATE utf8_unicode_ci         NOT NULL,
    `custom_3`                    text COLLATE utf8_unicode_ci         NOT NULL,
    `custom_4`                    text COLLATE utf8_unicode_ci         NOT NULL,
    `enable_checkout_order`       int(10) unsigned                              DEFAULT '1',
    `enable_see_pricing`          int(10) unsigned                              DEFAULT '1',
    `enable_save_order`           int(10) unsigned                              DEFAULT '1',
    `enable_request_quotation`    int(10) unsigned                              DEFAULT '1',
    `b2b_mode`                    int(10) unsigned                              DEFAULT '0',
    `joomla_user_group_id`        int(10) unsigned                              DEFAULT '0',
    `quotation_download`          varchar(1) COLLATE utf8_unicode_ci   NOT NULL DEFAULT '1',
    `quotation_email`             varchar(1) COLLATE utf8_unicode_ci   NOT NULL DEFAULT '1',
    PRIMARY KEY (`order_id`, `group_id`),
    CONSTRAINT `sltxh_cbcheckout_order_user_groups_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `sltxh_cbcheckout_order_users`
(
    `id`                   int(10) unsigned NOT NULL,
    `order_id`             int(10) unsigned NOT NULL DEFAULT '0',
    `companyname`          varchar(255)     NOT NULL,
    `gender`               varchar(1)       NOT NULL DEFAULT '1',
    `firstname`            varchar(255)     NOT NULL,
    `lastname`             varchar(255)     NOT NULL,
    `address1`             varchar(255)     NOT NULL,
    `address2`             varchar(255)     NOT NULL,
    `zipcode`              varchar(15)      NOT NULL,
    `city`                 varchar(255)     NOT NULL,
    `country`              int(10) unsigned          DEFAULT NULL,
    `email`                varchar(255)     NOT NULL,
    `phone`                varchar(255)     NOT NULL,
    `billingcompanyname`   varchar(255)     NOT NULL,
    `billingfirstname`     varchar(255)     NOT NULL,
    `billinglastname`      varchar(255)     NOT NULL,
    `billinggender`        varchar(1)                DEFAULT NULL,
    `billingaddress1`      varchar(255)     NOT NULL,
    `billingaddress2`      varchar(255)     NOT NULL,
    `billingzipcode`       varchar(15)      NOT NULL,
    `billingcity`          varchar(255)     NOT NULL,
    `billingcountry`       int(10) unsigned          DEFAULT NULL,
    `billingemail`         varchar(255)     NOT NULL,
    `billingphone`         varchar(255)     NOT NULL,
    `samedelivery`         int(11)                   DEFAULT '1',
    `created`              datetime         NOT NULL,
    `vatin`                varchar(200)     NOT NULL DEFAULT '',
    `group_id`             int(10) unsigned          DEFAULT '0',
    `newsletter`           int(10) unsigned          DEFAULT '0',
    `platform_user_id`     int(10) unsigned          DEFAULT '0',
    `salutation_id`        int(10) unsigned          DEFAULT NULL,
    `billingsalutation_id` int(10) unsigned          DEFAULT NULL,
    `state`                int(10) unsigned          DEFAULT NULL,
    `billingstate`         int(10) unsigned          DEFAULT NULL,
    `custom_1`             text             NOT NULL,
    `custom_2`             text             NOT NULL,
    `custom_3`             text             NOT NULL,
    `custom_4`             text             NOT NULL,
    `language_tag`         char(5)          NOT NULL,
    `county_id`            int(10) unsigned          DEFAULT NULL,
    `billingcounty_id`     int(10) unsigned          DEFAULT NULL,
    `city_id`              int(10) unsigned          DEFAULT NULL,
    `billingcity_id`       int(10) unsigned          DEFAULT NULL,
    PRIMARY KEY (`id`, `order_id`),
    KEY `salutation_id` (`salutation_id`),
    KEY `billingsalutation_id` (`billingsalutation_id`),
    KEY `group_id` (`group_id`),
    KEY `country` (`country`),
    KEY `billingcountry` (`billingcountry`),
    KEY `language_tag` (`language_tag`),
    KEY `order_id` (`order_id`),
    CONSTRAINT `sltxh_cbcheckout_order_users_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sltxh_cbcheckout_order_records` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_active_languages`
(
    `tag` char(5) NOT NULL,
    PRIMARY KEY (`tag`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_calculation_codes`
(
    `id`           int(10) unsigned NOT NULL AUTO_INCREMENT,
    `element_id_a` int(10) unsigned DEFAULT NULL,
    `element_id_b` int(10) unsigned DEFAULT NULL,
    `element_id_c` int(10) unsigned DEFAULT NULL,
    `element_id_d` int(10) unsigned DEFAULT NULL,
    `code`         text             NOT NULL,
    PRIMARY KEY (`id`),
    KEY `element_id_a` (`element_id_a`),
    KEY `element_id_b` (`element_id_b`),
    KEY `element_id_c` (`element_id_c`),
    KEY `element_id_d` (`element_id_d`),
    CONSTRAINT `sltxh_configbox_calculation_codes_ibfk_1` FOREIGN KEY (`id`) REFERENCES `sltxh_configbox_calculations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_calculation_codes_ibfk_2` FOREIGN KEY (`element_id_a`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_codes_ibfk_3` FOREIGN KEY (`element_id_b`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_codes_ibfk_4` FOREIGN KEY (`element_id_c`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_codes_ibfk_5` FOREIGN KEY (`element_id_d`) REFERENCES `sltxh_configbox_elements` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_calculation_formulas`
(
    `id`   int(10) unsigned NOT NULL,
    `calc` text             NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `sltxh_configbox_calculation_formulas_ibfk_1` FOREIGN KEY (`id`) REFERENCES `sltxh_configbox_calculations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_calculation_matrices`
(
    `id`                 int(10) unsigned NOT NULL AUTO_INCREMENT,
    `column_element_id`  int(10) unsigned          DEFAULT NULL,
    `row_element_id`     int(10) unsigned          DEFAULT NULL,
    `round`              int(10) unsigned          DEFAULT NULL,
    `lookup_value`       int(11)                   DEFAULT '0',
    `multiplicator`      decimal(20, 5)   NOT NULL DEFAULT '0.00000',
    `multielementid`     int(10) unsigned          DEFAULT NULL,
    `column_calc_id`     int(10) unsigned          DEFAULT NULL,
    `row_calc_id`        int(10) unsigned          DEFAULT NULL,
    `calcmodel_id_multi` int(10) unsigned          DEFAULT NULL,
    `row_type`           varchar(16)      NOT NULL DEFAULT 'none',
    `column_type`        varchar(16)      NOT NULL DEFAULT 'none',
    PRIMARY KEY (`id`),
    KEY `column_element_id` (`column_element_id`),
    KEY `row_element_id` (`row_element_id`),
    KEY `multielementid` (`multielementid`),
    KEY `column_calc_id` (`column_calc_id`),
    KEY `row_calc_id` (`row_calc_id`),
    KEY `calcmodel_id_multi` (`calcmodel_id_multi`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_1` FOREIGN KEY (`id`) REFERENCES `sltxh_configbox_calculations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_2` FOREIGN KEY (`column_element_id`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_3` FOREIGN KEY (`row_element_id`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_4` FOREIGN KEY (`multielementid`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_5` FOREIGN KEY (`column_calc_id`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_6` FOREIGN KEY (`row_calc_id`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_ibfk_7` FOREIGN KEY (`calcmodel_id_multi`) REFERENCES `sltxh_configbox_calculations` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_calculation_matrices_data`
(
    `id`       int(10) unsigned NOT NULL,
    `x`        bigint(20)       NOT NULL,
    `y`        bigint(20)       NOT NULL,
    `value`    decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `ordering` int(10) unsigned          DEFAULT NULL,
    PRIMARY KEY (`id`, `x`, `y`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_configbox_calculation_matrices_data_ibfk_1` FOREIGN KEY (`id`) REFERENCES `sltxh_configbox_calculations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_calculations`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`       varchar(200)     NOT NULL,
    `type`       varchar(10)      NOT NULL,
    `product_id` int(10) unsigned DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `sltxh_configbox_calculations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `sltxh_configbox_products` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_cart_position_configurations`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `cart_position_id` int(10) unsigned NOT NULL,
    `prod_id`          int(10) unsigned DEFAULT NULL,
    `element_id`       int(10) unsigned DEFAULT NULL,
    `selection`        varchar(2000)    NOT NULL,
    PRIMARY KEY (`id`),
    KEY `cart_position_id` (`cart_position_id`),
    KEY `prod_id` (`prod_id`),
    KEY `element_id` (`element_id`),
    CONSTRAINT `sltxh_configbox_cart_position_configurations_ibfk_1` FOREIGN KEY (`cart_position_id`) REFERENCES `sltxh_configbox_cart_positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_cart_position_configurations_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `sltxh_configbox_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_cart_position_configurations_ibfk_3` FOREIGN KEY (`element_id`) REFERENCES `sltxh_configbox_elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_cart_positions`
(
    `id`       int(10) unsigned NOT NULL AUTO_INCREMENT,
    `cart_id`  int(10) unsigned NOT NULL,
    `prod_id`  int(10) unsigned DEFAULT NULL,
    `quantity` int(10) unsigned DEFAULT '1',
    `created`  datetime         NOT NULL,
    `finished` int(11)          DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `prod_id` (`prod_id`),
    KEY `finished` (`finished`),
    KEY `created` (`created`),
    KEY `cart_id` (`cart_id`),
    CONSTRAINT `sltxh_configbox_cart_positions_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `sltxh_configbox_carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_cart_positions_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `sltxh_configbox_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_carts`
(
    `id`           int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`      int(10) unsigned NOT NULL,
    `created_time` datetime         NOT NULL,
    PRIMARY KEY (`id`),
    KEY `created_time` (`created_time`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `sltxh_configbox_carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sltxh_configbox_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_cities`
(
    `id`        int(10) unsigned NOT NULL AUTO_INCREMENT,
    `city_name` varchar(200)     NOT NULL DEFAULT '',
    `county_id` int(10) unsigned          DEFAULT NULL,
    `custom_1`  text             NOT NULL,
    `custom_2`  text             NOT NULL,
    `custom_3`  text             NOT NULL,
    `custom_4`  text             NOT NULL,
    `ordering`  int(11)                   DEFAULT NULL,
    `published` varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `state_id` (`county_id`),
    CONSTRAINT `sltxh_configbox_cities_ibfk_1` FOREIGN KEY (`county_id`) REFERENCES `sltxh_configbox_counties` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_config`
(
    `id`                                   int(11)          NOT NULL,
    `lastcleanup`                          bigint(20) unsigned       DEFAULT '0',
    `usertime`                             int(10) unsigned          DEFAULT '24',
    `unorderedtime`                        int(10) unsigned          DEFAULT '24',
    `intervals`                            int(10) unsigned          DEFAULT '12',
    `labelexpiry`                          int(10) unsigned          DEFAULT '28',
    `securecheckout`                       varchar(1)       NOT NULL DEFAULT '0',
    `weightunits`                          varchar(16)               DEFAULT '',
    `defaultprodimage`                     varchar(32)               DEFAULT '',
    `product_key`                          varchar(64)               DEFAULT '',
    `license_manager_satellites`           text,
    `page_nav_cart_button_last_page_only`  varchar(1)       NOT NULL DEFAULT '0',
    `page_nav_block_on_missing_selections` varchar(1)       NOT NULL DEFAULT '0',
    `label_element_custom_1`               varchar(255)     NOT NULL DEFAULT '',
    `label_element_custom_2`               varchar(255)     NOT NULL DEFAULT '',
    `label_element_custom_3`               varchar(255)     NOT NULL DEFAULT '',
    `label_element_custom_4`               varchar(255)     NOT NULL DEFAULT '',
    `label_element_custom_translatable_1`  varchar(255)     NOT NULL DEFAULT '',
    `label_element_custom_translatable_2`  varchar(255)     NOT NULL DEFAULT '',
    `label_assignment_custom_1`            varchar(255)     NOT NULL DEFAULT '',
    `label_assignment_custom_2`            varchar(255)     NOT NULL DEFAULT '',
    `label_assignment_custom_3`            varchar(255)     NOT NULL DEFAULT '',
    `label_assignment_custom_4`            varchar(255)     NOT NULL DEFAULT '',
    `label_option_custom_1`                varchar(255)     NOT NULL DEFAULT '',
    `label_option_custom_2`                varchar(255)     NOT NULL DEFAULT '',
    `label_option_custom_3`                varchar(255)     NOT NULL DEFAULT '',
    `label_option_custom_4`                varchar(255)     NOT NULL DEFAULT '',
    `use_internal_question_names`          varchar(1)       NOT NULL DEFAULT '0',
    `enable_geolocation`                   varchar(1)       NOT NULL DEFAULT '0',
    `geolocation_type`                     varchar(32)      NOT NULL DEFAULT 'maxmind_geoip2_db',
    `maxmind_license_key`                  varchar(200)     NOT NULL DEFAULT '',
    `maxmind_user_id`                      varchar(32)               DEFAULT '',
    `pm_show_delivery_options`             int(10) unsigned          DEFAULT '0',
    `pm_show_payment_options`              int(10) unsigned          DEFAULT '0',
    `label_product_custom_1`               varchar(200)     NOT NULL DEFAULT '',
    `label_product_custom_2`               varchar(200)     NOT NULL DEFAULT '',
    `label_product_custom_3`               varchar(200)     NOT NULL DEFAULT '',
    `label_product_custom_4`               varchar(200)     NOT NULL DEFAULT '',
    `label_product_custom_5`               varchar(200)     NOT NULL DEFAULT '',
    `label_product_custom_6`               varchar(200)     NOT NULL DEFAULT '',
    `enable_reviews_products`              varchar(1)       NOT NULL DEFAULT '0',
    `continue_listing_id`                  int(10) unsigned          DEFAULT NULL,
    `enable_performance_tracking`          varchar(1)       NOT NULL DEFAULT '0',
    `pm_show_regular_first`                varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_show_overview`             varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_show_prices`               varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_show_categories`           varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_show_elements`             varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_show_elementprices`        varchar(1)       NOT NULL DEFAULT '1',
    `pm_regular_expand_categories`         varchar(1)       NOT NULL DEFAULT '2',
    `pm_recurring_show_overview`           varchar(1)       NOT NULL DEFAULT '1',
    `pm_recurring_show_prices`             varchar(1)       NOT NULL DEFAULT '1',
    `pm_recurring_show_categories`         varchar(1)       NOT NULL DEFAULT '1',
    `pm_recurring_show_elements`           varchar(1)       NOT NULL DEFAULT '1',
    `pm_recurring_show_elementprices`      varchar(1)       NOT NULL DEFAULT '1',
    `pm_recurring_expand_categories`       varchar(1)       NOT NULL DEFAULT '2',
    `show_conversion_table`                varchar(1)       NOT NULL DEFAULT '0',
    `page_nav_show_tabs`                   varchar(1)       NOT NULL DEFAULT '0',
    `label_option_custom_5`                varchar(100)     NOT NULL DEFAULT '',
    `label_option_custom_6`                varchar(100)     NOT NULL DEFAULT '',
    `language_tag`                         char(5)          NOT NULL DEFAULT '',
    `pm_regular_show_taxes`                varchar(1)       NOT NULL DEFAULT '0',
    `pm_regular_show_cart_button`          varchar(1)       NOT NULL DEFAULT '0',
    `pm_recurring_show_taxes`              varchar(1)       NOT NULL DEFAULT '0',
    `pm_recurring_show_cart_button`        varchar(1)       NOT NULL DEFAULT '0',
    `pm_show_net_in_b2c`                   varchar(1)       NOT NULL DEFAULT '0',
    `review_notification_email`            varchar(255)     NOT NULL DEFAULT '',
    `default_customer_group_id`            int(10) unsigned          DEFAULT NULL,
    `default_country_id`                   int(10) unsigned          DEFAULT NULL,
    `disable_delivery`                     varchar(1)       NOT NULL DEFAULT '0',
    `sku_in_order_record`                  varchar(1)       NOT NULL DEFAULT '0',
    `newsletter_preset`                    varchar(1)       NOT NULL DEFAULT '0',
    `alternate_shipping_preset`            varchar(1)       NOT NULL DEFAULT '0',
    `show_recurring_login_cart`            varchar(1)       NOT NULL DEFAULT '0',
    `explicit_agreement_terms`             varchar(1)       NOT NULL DEFAULT '0',
    `explicit_agreement_rp`                varchar(1)       NOT NULL DEFAULT '0',
    `enable_invoicing`                     varchar(1)       NOT NULL DEFAULT '0',
    `send_invoice`                         varchar(1)       NOT NULL DEFAULT '0',
    `invoice_generation`                   varchar(1)       NOT NULL DEFAULT '0',
    `invoice_number_prefix`                varchar(32)      NOT NULL DEFAULT '',
    `invoice_number_start`                 int(10) unsigned NOT NULL DEFAULT '1',
    `page_nav_show_buttons`                varchar(1)       NOT NULL DEFAULT '1',
    `structureddata`                       varchar(1)       NOT NULL DEFAULT '1',
    `structureddata_in`                    varchar(16)      NOT NULL DEFAULT 'configurator',
    `use_ga_ecommerce`                     varchar(1)       NOT NULL DEFAULT '1',
    `ga_property_id`                       varchar(64)               DEFAULT '',
    `use_ga_enhanced_ecommerce`            varchar(1)       NOT NULL DEFAULT '0',
    `ga_behavior_offline_psps`             varchar(32)      NOT NULL DEFAULT 'conversion_when_ordered',
    `use_internal_answer_names`            varchar(1)       NOT NULL DEFAULT '0',
    `use_minified_js`                      varchar(1)       NOT NULL DEFAULT '1',
    `use_minified_css`                     varchar(1)       NOT NULL DEFAULT '1',
    `use_assets_cache_buster`              varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `default_customer_group_id` (`default_customer_group_id`),
    KEY `default_country_id` (`default_country_id`),
    KEY `continue_listing_id` (`continue_listing_id`),
    CONSTRAINT `sltxh_configbox_config_ibfk_1` FOREIGN KEY (`default_customer_group_id`) REFERENCES `sltxh_configbox_groups` (`id`),
    CONSTRAINT `sltxh_configbox_config_ibfk_2` FOREIGN KEY (`default_country_id`) REFERENCES `sltxh_configbox_countries` (`id`),
    CONSTRAINT `sltxh_configbox_config_ibfk_3` FOREIGN KEY (`continue_listing_id`) REFERENCES `sltxh_configbox_listings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_connectors`
(
    `id`           int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`         varchar(100)     NOT NULL,
    `ordering`     int(11)          DEFAULT NULL,
    `published`    int(10) unsigned DEFAULT '1',
    `after_system` int(10) unsigned DEFAULT '1',
    `file`         varchar(500)     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `published` (`published`),
    KEY `ordering` (`ordering`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_counties`
(
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `county_name` varchar(200)     NOT NULL DEFAULT '',
    `state_id`    int(10) unsigned          DEFAULT NULL,
    `custom_1`    text             NOT NULL,
    `custom_2`    text             NOT NULL,
    `custom_3`    text             NOT NULL,
    `custom_4`    text             NOT NULL,
    `ordering`    int(11)                   DEFAULT NULL,
    `published`   varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `state_id` (`state_id`),
    CONSTRAINT `sltxh_configbox_counties_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `sltxh_configbox_states` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_countries`
(
    `id`             int(10) unsigned NOT NULL,
    `country_name`   varchar(64)               DEFAULT NULL,
    `country_3_code` char(3)                   DEFAULT NULL,
    `country_2_code` char(2)                   DEFAULT NULL,
    `vat_free`       varchar(1)       NOT NULL DEFAULT '1',
    `in_eu_vat_area` varchar(1)       NOT NULL DEFAULT '0',
    `published`      varchar(1)       NOT NULL DEFAULT '1',
    `ordering`       int(11)                   DEFAULT '0',
    `custom_1`       text             NOT NULL,
    `custom_2`       text             NOT NULL,
    `custom_3`       text             NOT NULL,
    `custom_4`       text             NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_country_name` (`country_name`),
    KEY `ordering` (`ordering`),
    KEY `published` (`published`),
    KEY `country_2_code` (`country_2_code`),
    KEY `vat_free` (`vat_free`),
    KEY `in_eu_vat_area` (`in_eu_vat_area`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_currencies`
(
    `id`            int(10) unsigned        NOT NULL AUTO_INCREMENT,
    `base`          int(11)                          DEFAULT '0',
    `multiplicator` decimal(10, 5) unsigned NOT NULL DEFAULT '1.00000',
    `symbol`        varchar(10)             NOT NULL,
    `code`          varchar(10)             NOT NULL,
    `default`       int(11)                          DEFAULT '0',
    `ordering`      int(11)                          DEFAULT NULL,
    `published`     int(11)                          DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    KEY `published` (`published`),
    KEY `ordering` (`ordering`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_customers`
(
    `id`                      int(10) unsigned NOT NULL,
    `billing_company_name`    varchar(128)              DEFAULT '',
    `billing_first_name`      varchar(128)              DEFAULT '',
    `billing_last_name`       varchar(128)              DEFAULT '',
    `billing_address_1`       varchar(128)              DEFAULT '',
    `billing_address_2`       varchar(128)              DEFAULT '',
    `billing_postal_code`     varchar(128)              DEFAULT '',
    `billing_email`           varchar(128)              DEFAULT '',
    `billing_phone`           varchar(128)              DEFAULT '',
    `billing_salutation_id`   int(10) unsigned          DEFAULT NULL,
    `billing_salutation_text` varchar(128)              DEFAULT '',
    `billing_gender`          varchar(16)      NOT NULL DEFAULT 'na',
    `billing_city_id`         int(10) unsigned          DEFAULT NULL,
    `billing_county_id`       int(10) unsigned          DEFAULT NULL,
    `billing_state_id`        int(10) unsigned          DEFAULT NULL,
    `billing_country_id`      int(10) unsigned          DEFAULT NULL,
    `shipping_company_name`   varchar(128)              DEFAULT '',
    `shipping_first_name`     varchar(128)              DEFAULT '',
    `shipping_last_name`      varchar(128)              DEFAULT '',
    `shipping_address_1`      varchar(128)              DEFAULT '',
    `shipping_address_2`      varchar(128)              DEFAULT '',
    `shipping_postal_code`    varchar(128)              DEFAULT '',
    `shipping_email`          varchar(128)              DEFAULT '',
    `shipping_phone`          varchar(128)              DEFAULT '',
    `shipping_salutation_id`  int(10) unsigned          DEFAULT NULL,
    `shipping_city_id`        int(10) unsigned          DEFAULT NULL,
    `shipping_county_id`      int(10) unsigned          DEFAULT NULL,
    `shipping_state_id`       int(10) unsigned          DEFAULT NULL,
    `shipping_country_id`     int(10) unsigned          DEFAULT NULL,
    `same_delivery`           varchar(1)       NOT NULL DEFAULT '1',
    `vatin`                   varchar(128)              DEFAULT '',
    `group_id`                int(10) unsigned          DEFAULT NULL,
    `platform_user_id`        int(10) unsigned          DEFAULT NULL,
    `language_tag`            char(5)                   DEFAULT NULL,
    `newsletter`              varchar(1)       NOT NULL DEFAULT '0',
    `custom_1`                text,
    `custom_2`                text,
    `custom_3`                text,
    `custom_4`                text,
    `custom_5`                text,
    `custom_6`                text,
    `custom_7`                text,
    `custom_8`                text,
    `is_temporary`            varchar(1)       NOT NULL DEFAULT '1',
    `created_on`              datetime                  DEFAULT NULL,
    `password`                varchar(300)              DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `group_id` (`group_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_elements`
(
    `id`                        int(10) unsigned NOT NULL AUTO_INCREMENT,
    `page_id`                   int(10) unsigned          DEFAULT NULL,
    `el_image`                  varchar(100)     NOT NULL,
    `required`                  int(11)                   DEFAULT NULL,
    `validate`                  int(11)                   DEFAULT NULL,
    `minval`                    varchar(255)              DEFAULT NULL,
    `maxval`                    varchar(255)              DEFAULT NULL,
    `calcmodel`                 int(10) unsigned          DEFAULT NULL,
    `calcmodel_recurring`       int(10) unsigned          DEFAULT NULL,
    `multiplicator`             float            NOT NULL,
    `published`                 int(11)                   DEFAULT NULL,
    `ordering`                  int(11)                   DEFAULT NULL,
    `asproducttitle`            int(10) unsigned          DEFAULT '0',
    `default_value`             text             NOT NULL,
    `show_in_overview`          int(11)                   DEFAULT '1',
    `text_calcmodel`            int(11)                   DEFAULT '0',
    `element_custom_1`          varchar(255)     NOT NULL DEFAULT '',
    `element_custom_2`          varchar(255)     NOT NULL DEFAULT '',
    `element_custom_3`          varchar(255)     NOT NULL DEFAULT '',
    `element_custom_4`          varchar(255)     NOT NULL DEFAULT '',
    `rules`                     text             NOT NULL,
    `internal_name`             varchar(255)     NOT NULL DEFAULT '',
    `element_css_classes`       varchar(100)     NOT NULL DEFAULT '',
    `calcmodel_id_min_val`      int(10) unsigned          DEFAULT NULL,
    `calcmodel_id_max_val`      int(10) unsigned          DEFAULT NULL,
    `upload_extensions`         varchar(255)     NOT NULL DEFAULT 'png, jpg, jpeg, gif, tif',
    `upload_mime_types`         varchar(255)     NOT NULL DEFAULT '',
    `upload_size_mb`            float unsigned   NOT NULL DEFAULT '1',
    `slider_steps`              float unsigned   NOT NULL DEFAULT '1',
    `calcmodel_weight`          int(10) unsigned          DEFAULT NULL,
    `choices`                   text             NOT NULL,
    `desc_display_method`       varchar(1)       NOT NULL DEFAULT '1',
    `behavior_on_activation`    varchar(16)      NOT NULL DEFAULT 'none',
    `behavior_on_changes`       varchar(16)      NOT NULL DEFAULT 'silent',
    `question_type`             varchar(64)      NOT NULL DEFAULT '',
    `prefill_on_init`           varchar(1)       NOT NULL DEFAULT '0',
    `input_restriction`         varchar(16)      NOT NULL DEFAULT 'plaintext',
    `set_min_value`             varchar(16)      NOT NULL DEFAULT 'none',
    `set_max_value`             varchar(16)      NOT NULL DEFAULT 'none',
    `show_unit`                 varchar(1)       NOT NULL DEFAULT '0',
    `title_display`             varchar(16)      NOT NULL DEFAULT 'heading',
    `is_shapediver_control`     varchar(1)       NOT NULL DEFAULT '0',
    `shapediver_parameter_id`   varchar(255)     NOT NULL DEFAULT '',
    `behavior_on_inconsistency` varchar(32)      NOT NULL DEFAULT 'deselect',
    `display_while_disabled`    varchar(16)      NOT NULL DEFAULT 'hide',
    `shapediver_geometry_name`  varchar(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `ordering` (`ordering`),
    KEY `page_id-ordering` (`page_id`, `ordering`),
    KEY `calcmodel_id_min_val` (`calcmodel_id_min_val`),
    KEY `calcmodel_id_max_val` (`calcmodel_id_max_val`),
    KEY `calcmodel` (`calcmodel`),
    KEY `calcmodel_recurring` (`calcmodel_recurring`),
    KEY `calcmodel_weight` (`calcmodel_weight`),
    KEY `is_shapediver_control` (`is_shapediver_control`),
    KEY `published` (`published`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `sltxh_configbox_pages` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_10` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_11` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_12` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_13` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_14` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_15` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_16` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_17` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_18` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_19` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_2` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_20` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_21` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_22` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_23` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_24` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_25` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_26` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_27` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_28` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_29` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_3` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_30` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_31` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_32` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_33` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_34` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_35` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_36` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_4` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_5` FOREIGN KEY (`calcmodel_id_max_val`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_6` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_7` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_8` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_elements_ibfk_9` FOREIGN KEY (`calcmodel_id_min_val`) REFERENCES `sltxh_configbox_calculations` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_examples`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `product_id` int(10) unsigned NOT NULL,
    `published`  int(10) unsigned DEFAULT NULL,
    `ordering`   int(11)          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_external_product_appends`
(
    `product_id` int(10) unsigned NOT NULL,
    `test_image` varchar(100) DEFAULT '',
    PRIMARY KEY (`product_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_groups`
(
    `id`                          int(10) unsigned        NOT NULL AUTO_INCREMENT,
    `title`                       varchar(255)            NOT NULL,
    `discount_start_1`            decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `discount_factor_1`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_start_2`            decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `discount_factor_2`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_start_3`            decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `discount_factor_3`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_start_4`            decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `discount_factor_4`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_start_5`            decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `discount_factor_5`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `custom_1`                    text                    NOT NULL,
    `custom_2`                    text                    NOT NULL,
    `custom_3`                    text                    NOT NULL,
    `custom_4`                    text                    NOT NULL,
    `enable_checkout_order`       varchar(1)              NOT NULL DEFAULT '1',
    `enable_see_pricing`          varchar(1)              NOT NULL DEFAULT '1',
    `enable_save_order`           varchar(1)              NOT NULL DEFAULT '1',
    `enable_request_quotation`    varchar(1)              NOT NULL DEFAULT '1',
    `b2b_mode`                    varchar(1)              NOT NULL DEFAULT '1',
    `joomla_user_group_id`        int(10) unsigned                 DEFAULT '0',
    `quotation_download`          varchar(1)              NOT NULL DEFAULT '1',
    `quotation_email`             varchar(1)              NOT NULL DEFAULT '1',
    `discount_amount_1`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_amount_2`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_amount_3`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_amount_4`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_amount_5`           decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_type_1`             varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_type_2`             varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_type_3`             varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_type_4`             varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_type_5`             varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_recurring_start_1`  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_2`  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_3`  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_4`  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_start_5`  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_1` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_2` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_3` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_4` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_factor_5` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_1` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_2` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_3` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_4` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_amount_5` decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `discount_recurring_type_1`   varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_2`   varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_3`   varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_4`   varchar(32)             NOT NULL DEFAULT 'percentage',
    `discount_recurring_type_5`   varchar(32)             NOT NULL DEFAULT 'percentage',
    PRIMARY KEY (`id`),
    KEY `joomla_user_group_id` (`joomla_user_group_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_listings`
(
    `id`              int(10) unsigned NOT NULL AUTO_INCREMENT,
    `layoutname`      varchar(128)     NOT NULL DEFAULT 'default',
    `published`       varchar(1)       NOT NULL DEFAULT '1',
    `product_sorting` varchar(1)       NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `published` (`published`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_magento_xref_mprod_cbprod`
(
    `id` int unsigned auto_increment primary key,
    `cb_product_id` int unsigned default 0 not null comment 'CB Product Id',
    `magento_product_id` int unsigned default 0 not null comment 'Magento Product ID'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_notifications`
(
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`          varchar(40)      NOT NULL,
    `type`          varchar(50)      NOT NULL,
    `statuscode`    int(11) DEFAULT NULL,
    `send_customer` int(11) DEFAULT '1',
    `send_manager`  int(11) DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `statuscode` (`statuscode`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_oldlabels`
(
    `id`           int(10) unsigned    NOT NULL AUTO_INCREMENT,
    `type`         int(11) unsigned    NOT NULL,
    `key`          int(10) unsigned DEFAULT NULL,
    `label`        varchar(255)        NOT NULL,
    `prod_id`      int(10) unsigned DEFAULT NULL,
    `created`      bigint(20) unsigned NOT NULL,
    `language_tag` char(5)             NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniqe_strings` (`type`, `key`, `language_tag`),
    KEY `created` (`created`),
    KEY `prod_id` (`prod_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_options`
(
    `id`                        int(10) unsigned NOT NULL AUTO_INCREMENT,
    `sku`                       varchar(60)      NOT NULL DEFAULT '',
    `price`                     decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `price_overrides`           varchar(1024)    NOT NULL DEFAULT '[]',
    `price_recurring`           decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `price_recurring_overrides` varchar(1024)    NOT NULL DEFAULT '[]',
    `weight`                    decimal(20, 3)   NOT NULL DEFAULT '0.000',
    `option_custom_1`           varchar(255)     NOT NULL DEFAULT '',
    `option_custom_2`           varchar(255)     NOT NULL DEFAULT '',
    `option_custom_3`           varchar(255)     NOT NULL DEFAULT '',
    `option_custom_4`           varchar(255)     NOT NULL DEFAULT '',
    `option_image`              varchar(200)     NOT NULL DEFAULT '',
    `available`                 varchar(1)       NOT NULL DEFAULT '0',
    `availibility_date`         date                      DEFAULT NULL,
    `was_price`                 decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `was_price_recurring`       decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `disable_non_available`     varchar(1)       NOT NULL DEFAULT '0',
    `desc_display_method`       varchar(16)      NOT NULL DEFAULT 'tooltip',
    PRIMARY KEY (`id`),
    KEY `sku` (`sku`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_pages`
(
    `id`                 int(10) unsigned NOT NULL AUTO_INCREMENT,
    `visualization_view` varchar(100)     NOT NULL DEFAULT '',
    `layoutname`         varchar(100)     NOT NULL,
    `published`          varchar(1)       NOT NULL DEFAULT '0',
    `ordering`           int(11)                   DEFAULT '0',
    `product_id`         int(10) unsigned          DEFAULT NULL,
    `css_classes`        varchar(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `published` (`published`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_configbox_pages_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `sltxh_configbox_products` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_payment_methods`
(
    `id`             int(10) unsigned NOT NULL AUTO_INCREMENT,
    `price`          decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `taxclass_id`    int(10) unsigned          DEFAULT NULL,
    `params`         varchar(255)     NOT NULL,
    `ordering`       int(11)                   DEFAULT '0',
    `published`      varchar(1)       NOT NULL DEFAULT '0',
    `percentage`     decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `price_min`      decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `price_max`      decimal(20, 4)   NOT NULL DEFAULT '0.0000',
    `connector_name` varchar(50)      NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `taxclass_id` (`taxclass_id`),
    KEY `published` (`published`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_configbox_payment_methods_ibfk_1` FOREIGN KEY (`taxclass_id`) REFERENCES `sltxh_configbox_tax_classes` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_product_detail_panes`
(
    `id`                    int(10) unsigned NOT NULL AUTO_INCREMENT,
    `product_id`            int(10) unsigned NOT NULL,
    `heading_icon_filename` varchar(30)      NOT NULL,
    `css_classes`           varchar(255)     NOT NULL DEFAULT '',
    `ordering`              int(11)                   DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_configbox_product_detail_panes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `sltxh_configbox_products` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_products`
(
    `id`                                         int(10) unsigned        NOT NULL AUTO_INCREMENT,
    `sku`                                        varchar(60)             NOT NULL,
    `prod_image`                                 varchar(50)             NOT NULL,
    `baseimage`                                  varchar(100)            NOT NULL,
    `opt_image_x`                                int(10) unsigned                 DEFAULT NULL,
    `opt_image_y`                                int(10) unsigned                 DEFAULT NULL,
    `baseprice`                                  decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `baseprice_recurring`                        decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `baseprice_overrides`                        varchar(1024)           NOT NULL DEFAULT '[]',
    `baseprice_recurring_overrides`              varchar(1024)           NOT NULL DEFAULT '[]',
    `baseweight`                                 decimal(20, 3) unsigned NOT NULL DEFAULT '0.000',
    `taxclass_id`                                int(10) unsigned                 DEFAULT NULL,
    `taxclass_recurring_id`                      int(10) unsigned                 DEFAULT NULL,
    `layoutname`                                 varchar(100)            NOT NULL,
    `published`                                  varchar(1)              NOT NULL DEFAULT '0',
    `pm_show_delivery_options`                   varchar(1)              NOT NULL DEFAULT '2',
    `pm_show_payment_options`                    varchar(1)              NOT NULL DEFAULT '2',
    `product_custom_1`                           text                    NOT NULL,
    `product_custom_2`                           text                    NOT NULL,
    `product_custom_3`                           text                    NOT NULL,
    `product_custom_4`                           text                    NOT NULL,
    `enable_reviews`                             varchar(1)              NOT NULL DEFAULT '2',
    `external_reviews_id`                        varchar(200)            NOT NULL DEFAULT '',
    `dispatch_time`                              int(10) unsigned                 DEFAULT NULL,
    `pm_show_regular_first`                      int(11)                          DEFAULT '2',
    `pm_regular_show_overview`                   int(11)                          DEFAULT '2',
    `pm_regular_show_prices`                     int(11)                          DEFAULT '2',
    `pm_regular_show_categories`                 int(11)                          DEFAULT '2',
    `pm_regular_show_elements`                   int(11)                          DEFAULT '2',
    `pm_regular_show_elementprices`              int(11)                          DEFAULT '2',
    `pm_regular_expand_categories`               int(11)                          DEFAULT '2',
    `pm_recurring_show_overview`                 int(11)                          DEFAULT '2',
    `pm_recurring_show_prices`                   int(11)                          DEFAULT '2',
    `pm_recurring_show_categories`               int(11)                          DEFAULT '2',
    `pm_recurring_show_elements`                 int(11)                          DEFAULT '2',
    `pm_recurring_show_elementprices`            int(11)                          DEFAULT '2',
    `pm_recurring_expand_categories`             int(11)                          DEFAULT '3',
    `page_nav_show_tabs`                         varchar(1)              NOT NULL DEFAULT '2',
    `show_buy_button`                            varchar(1)              NOT NULL DEFAULT '1',
    `was_price`                                  decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `was_price_recurring`                        decimal(20, 4)          NOT NULL DEFAULT '0.0000',
    `pm_show_net_in_b2c`                         varchar(1)              NOT NULL DEFAULT '0',
    `pm_regular_show_taxes`                      varchar(1)              NOT NULL DEFAULT '2',
    `pm_regular_show_cart_button`                varchar(1)              NOT NULL DEFAULT '2',
    `pm_recurring_show_taxes`                    varchar(1)              NOT NULL DEFAULT '2',
    `pm_recurring_show_cart_button`              varchar(1)              NOT NULL DEFAULT '2',
    `product_detail_panes_method`                varchar(16)             NOT NULL DEFAULT 'tabs',
    `product_detail_panes_in_listings`           varchar(1)              NOT NULL DEFAULT '0',
    `product_detail_panes_in_product_pages`      varchar(1)              NOT NULL DEFAULT '1',
    `product_detail_panes_in_configurator_steps` varchar(1)              NOT NULL DEFAULT '0',
    `page_nav_show_buttons`                      varchar(1)              NOT NULL DEFAULT '2',
    `page_nav_block_on_missing_selections`       varchar(1)              NOT NULL DEFAULT '2',
    `page_nav_cart_button_last_page_only`        varchar(1)              NOT NULL DEFAULT '2',
    `visualization_type`                         varchar(16)             NOT NULL DEFAULT 'none',
    `shapediver_model_data`                      text                    NOT NULL,
    `use_recurring_pricing`                      varchar(1)              NOT NULL DEFAULT '0',
    `show_product_details_button`                varchar(1)              NOT NULL DEFAULT '0',
    `product_details_page_type`                  varchar(16)             NOT NULL DEFAULT 'none',
    `magento_product_id`                         int(10) unsigned                 DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `taxclass_id` (`taxclass_id`),
    KEY `taxclass_recurring_id` (`taxclass_recurring_id`),
    KEY `published` (`published`),
    KEY `magento_product_id` (`magento_product_id`),
    CONSTRAINT `sltxh_configbox_products_ibfk_1` FOREIGN KEY (`taxclass_id`) REFERENCES `sltxh_configbox_tax_classes` (`id`),
    CONSTRAINT `sltxh_configbox_products_ibfk_2` FOREIGN KEY (`taxclass_recurring_id`) REFERENCES `sltxh_configbox_tax_classes` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_reviews`
(
    `id`           int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`         varchar(100)     NOT NULL,
    `rating`       decimal(2, 1)    NOT NULL,
    `comment`      text             NOT NULL,
    `published`    varchar(1)       NOT NULL DEFAULT '0',
    `language_tag` varchar(5)       NOT NULL,
    `date_created` datetime         NOT NULL COMMENT 'UTC',
    `product_id`   int(10) unsigned          DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `published` (`published`, `language_tag`),
    KEY `product_id` (`product_id`),
    KEY `date_created` (`date_created`),
    CONSTRAINT `sltxh_configbox_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `sltxh_configbox_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_salutations`
(
    `id`     int(10) unsigned NOT NULL AUTO_INCREMENT,
    `gender` varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `sltxh_configbox_session`
(
    `id`         varchar(128)        NOT NULL,
    `user_agent` varchar(200)        NOT NULL DEFAULT '',
    `ip_address` varchar(100)        NOT NULL DEFAULT '',
    `data`       text                NOT NULL,
    `updated`    bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `updated` (`updated`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_shippers`
(
    `id`        int(10) unsigned NOT NULL AUTO_INCREMENT,
    `published` varchar(1)       NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `published` (`published`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_shipping_methods`
(
    `id`           int(10) unsigned        NOT NULL AUTO_INCREMENT,
    `shipper_id`   int(10) unsigned                 DEFAULT NULL,
    `zone_id`      int(10) unsigned                 DEFAULT NULL,
    `minweight`    decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `maxweight`    decimal(20, 4) unsigned NOT NULL DEFAULT '0.0000',
    `deliverytime` int(10) unsigned                 DEFAULT '0',
    `price`        float                   NOT NULL,
    `taxclass_id`  int(10) unsigned                 DEFAULT NULL,
    `published`    varchar(1)              NOT NULL DEFAULT '0',
    `external_id`  varchar(100)            NOT NULL DEFAULT '',
    `ordering`     int(11)                          DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `external_id` (`external_id`),
    KEY `ordering` (`ordering`),
    KEY `shipper_id` (`shipper_id`),
    KEY `taxclass_id` (`taxclass_id`),
    KEY `zone_id` (`zone_id`),
    KEY `published` (`published`),
    CONSTRAINT `sltxh_configbox_shipping_methods_ibfk_1` FOREIGN KEY (`shipper_id`) REFERENCES `sltxh_configbox_shippers` (`id`),
    CONSTRAINT `sltxh_configbox_shipping_methods_ibfk_2` FOREIGN KEY (`taxclass_id`) REFERENCES `sltxh_configbox_tax_classes` (`id`),
    CONSTRAINT `sltxh_configbox_shipping_methods_ibfk_3` FOREIGN KEY (`zone_id`) REFERENCES `sltxh_configbox_zones` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_shopdata`
(
    `id`                    int(10) unsigned NOT NULL,
    `shopname`              varchar(200)     NOT NULL,
    `shoplogo`              varchar(100)     NOT NULL,
    `shopaddress1`          varchar(200)     NOT NULL,
    `shopaddress2`          varchar(200)     NOT NULL,
    `shopzipcode`           varchar(40)      NOT NULL,
    `shopcity`              varchar(100)     NOT NULL,
    `country_id`            int(10) unsigned DEFAULT NULL,
    `state_id`              int(10) unsigned DEFAULT NULL,
    `shopphonesales`        varchar(100)     NOT NULL,
    `shopphonesupport`      varchar(100)     NOT NULL,
    `shopemailsales`        varchar(100)     NOT NULL,
    `shopemailsupport`      varchar(100)     NOT NULL,
    `shoplinktotc`          varchar(150)     NOT NULL,
    `shopfax`               varchar(100)     NOT NULL,
    `shopbankname`          varchar(100)     NOT NULL,
    `shopbankaccountholder` varchar(100)     NOT NULL,
    `shopbankaccount`       varchar(100)     NOT NULL,
    `shopbankcode`          varchar(100)     NOT NULL,
    `shopbic`               varchar(100)     NOT NULL,
    `shopiban`              varchar(100)     NOT NULL,
    `shopuid`               varchar(100)     NOT NULL,
    `shopcomreg`            varchar(100)     NOT NULL,
    `shopwebsite`           varchar(255)     DEFAULT NULL,
    `shopowner`             varchar(255)     DEFAULT NULL,
    `shoplegalvenue`        varchar(255)     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `state_id` (`state_id`),
    KEY `country_id` (`country_id`),
    CONSTRAINT `sltxh_configbox_shopdata_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `sltxh_configbox_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_shopdata_ibfk_2` FOREIGN KEY (`country_id`) REFERENCES `sltxh_configbox_countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_states`
(
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `country_id`  int(10) unsigned          DEFAULT NULL,
    `name`        varchar(50)      NOT NULL DEFAULT '',
    `iso_code`    varchar(50)      NOT NULL DEFAULT '',
    `fips_number` varchar(5)       NOT NULL DEFAULT '',
    `custom_1`    varchar(255)              DEFAULT '',
    `custom_2`    varchar(255)              DEFAULT '',
    `custom_3`    varchar(255)              DEFAULT '',
    `custom_4`    varchar(255)              DEFAULT '',
    `ordering`    int(11)                   DEFAULT '0',
    `published`   varchar(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `country_id` (`country_id`),
    KEY `ordering` (`ordering`, `published`),
    KEY `iso_fips` (`iso_code`, `fips_number`),
    KEY `published` (`published`),
    CONSTRAINT `sltxh_configbox_states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `sltxh_configbox_countries` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_strings`
(
    `type`         int(10) unsigned DEFAULT NULL,
    `key`          int(10) unsigned NOT NULL COMMENT 'Primary key value for the regarding record.',
    `language_tag` char(5)          NOT NULL,
    `text`         text             NOT NULL,
    UNIQUE KEY `uniqe_strings` (`type`, `key`, `language_tag`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `sltxh_configbox_system_vars`
(
    `key`   varchar(128) NOT NULL,
    `value` text         NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_tax_class_rates`
(
    `tax_class_id` int(10) unsigned                DEFAULT NULL,
    `city_id`      int(10) unsigned                DEFAULT NULL,
    `county_id`    int(10) unsigned                DEFAULT NULL,
    `state_id`     int(10) unsigned                DEFAULT NULL,
    `country_id`   int(10) unsigned                DEFAULT NULL,
    `tax_rate`     decimal(4, 2) unsigned NOT NULL DEFAULT '0.00',
    `tax_code`     varchar(100)           NOT NULL DEFAULT '',
    UNIQUE KEY `unique_all` (`tax_class_id`, `city_id`, `county_id`, `state_id`, `country_id`),
    KEY `city_id` (`city_id`),
    KEY `county_id` (`county_id`),
    KEY `state_id` (`state_id`),
    KEY `country_id` (`country_id`),
    CONSTRAINT `sltxh_configbox_tax_class_rates_ibfk_1` FOREIGN KEY (`tax_class_id`) REFERENCES `sltxh_configbox_tax_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_tax_class_rates_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `sltxh_configbox_cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_tax_class_rates_ibfk_3` FOREIGN KEY (`county_id`) REFERENCES `sltxh_configbox_counties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_tax_class_rates_ibfk_4` FOREIGN KEY (`state_id`) REFERENCES `sltxh_configbox_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_tax_class_rates_ibfk_5` FOREIGN KEY (`country_id`) REFERENCES `sltxh_configbox_countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = DYNAMIC;

CREATE TABLE `sltxh_configbox_tax_classes`
(
    `id`               int(10) unsigned       NOT NULL AUTO_INCREMENT,
    `title`            varchar(255)           NOT NULL,
    `default_tax_rate` decimal(4, 2) unsigned NOT NULL,
    `id_external`      varchar(100)           NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_user_field_definitions`
(
    `id`                 int(10) unsigned NOT NULL AUTO_INCREMENT,
    `field_name`         varchar(30)      NOT NULL,
    `show_checkout`      varchar(1)       NOT NULL DEFAULT '0',
    `require_checkout`   varchar(1)       NOT NULL DEFAULT '0',
    `show_quotation`     varchar(1)       NOT NULL DEFAULT '0',
    `require_quotation`  varchar(1)       NOT NULL DEFAULT '0',
    `show_saveorder`     varchar(1)       NOT NULL DEFAULT '0',
    `require_saveorder`  varchar(1)       NOT NULL DEFAULT '0',
    `show_profile`       varchar(1)       NOT NULL DEFAULT '0',
    `require_profile`    varchar(1)       NOT NULL DEFAULT '0',
    `validation_browser` varchar(255)     NOT NULL DEFAULT '',
    `validation_server`  varchar(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_users`
(
    `id`                   int(10) unsigned NOT NULL AUTO_INCREMENT,
    `platform_user_id`     int(10) unsigned          DEFAULT NULL,
    `companyname`          varchar(255)     NOT NULL,
    `gender`               varchar(1)       NOT NULL DEFAULT '1',
    `firstname`            varchar(255)     NOT NULL,
    `lastname`             varchar(255)     NOT NULL,
    `address1`             varchar(255)     NOT NULL,
    `address2`             varchar(255)     NOT NULL,
    `zipcode`              varchar(15)      NOT NULL,
    `city`                 varchar(255)     NOT NULL,
    `country`              int(10) unsigned          DEFAULT NULL,
    `email`                varchar(255)     NOT NULL,
    `phone`                varchar(255)     NOT NULL,
    `billingcompanyname`   varchar(255)     NOT NULL,
    `billingfirstname`     varchar(255)     NOT NULL,
    `billinglastname`      varchar(255)     NOT NULL,
    `billinggender`        varchar(1)                DEFAULT NULL,
    `billingaddress1`      varchar(255)     NOT NULL,
    `billingaddress2`      varchar(255)     NOT NULL,
    `billingzipcode`       varchar(15)      NOT NULL,
    `billingcity`          varchar(255)     NOT NULL,
    `billingcountry`       int(10) unsigned          DEFAULT NULL,
    `billingemail`         varchar(255)     NOT NULL,
    `billingphone`         varchar(255)     NOT NULL,
    `samedelivery`         int(11)                   DEFAULT '1',
    `created`              datetime         NOT NULL,
    `password`             varchar(300)     NOT NULL,
    `vatin`                varchar(200)     NOT NULL,
    `group_id`             int(10) unsigned          DEFAULT NULL,
    `newsletter`           int(10) unsigned          DEFAULT '0',
    `is_temporary`         int(10) unsigned          DEFAULT '0',
    `salutation_id`        int(10) unsigned          DEFAULT NULL,
    `billingsalutation_id` int(10) unsigned          DEFAULT NULL,
    `state`                int(10) unsigned          DEFAULT NULL,
    `billingstate`         int(10) unsigned          DEFAULT NULL,
    `custom_1`             varchar(255)              DEFAULT '',
    `custom_2`             varchar(255)              DEFAULT '',
    `custom_3`             varchar(255)              DEFAULT '',
    `custom_4`             varchar(255)              DEFAULT '',
    `language_tag`         char(5)          NOT NULL,
    `county_id`            int(10) unsigned          DEFAULT NULL,
    `billingcounty_id`     int(10) unsigned          DEFAULT NULL,
    `city_id`              int(10) unsigned          DEFAULT NULL,
    `billingcity_id`       int(10) unsigned          DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `salutation_id` (`salutation_id`),
    KEY `billingsalutation_id` (`billingsalutation_id`),
    KEY `group_id` (`group_id`),
    KEY `country` (`country`),
    KEY `billingcountry` (`billingcountry`),
    KEY `language_tag` (`language_tag`),
    KEY `is_temporary` (`is_temporary`),
    KEY `created` (`created`),
    KEY `platform_user_id` (`platform_user_id`),
    KEY `state` (`state`),
    KEY `billingstate` (`billingstate`),
    KEY `county_id` (`county_id`),
    KEY `billingcounty_id` (`billingcounty_id`),
    KEY `city_id` (`city_id`),
    KEY `billingcity_id` (`billingcity_id`),
    CONSTRAINT `sltxh_configbox_users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `sltxh_configbox_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_xref_country_payment_method`
(
    `payment_id` int(10) unsigned NOT NULL,
    `country_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`country_id`, `payment_id`),
    KEY `sltxh_configbox_xref_country_payment_method_ibfk_2` (`payment_id`),
    CONSTRAINT `sltxh_configbox_xref_country_payment_method_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `sltxh_configbox_countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_xref_country_payment_method_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `sltxh_configbox_payment_methods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_xref_country_zone`
(
    `zone_id`    int(10) unsigned NOT NULL,
    `country_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`country_id`, `zone_id`),
    KEY `zone_id` (`zone_id`),
    CONSTRAINT `sltxh_configbox_xref_country_zone_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `sltxh_configbox_countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_xref_country_zone_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `sltxh_configbox_zones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_xref_element_option`
(
    `id`                                    int(10) unsigned NOT NULL AUTO_INCREMENT,
    `element_id`                            int(10) unsigned          DEFAULT NULL,
    `option_id`                             int(10) unsigned          DEFAULT NULL,
    `default`                               int(1) unsigned  NOT NULL,
    `visualization_image`                   varchar(100)     NOT NULL DEFAULT '',
    `visualization_stacking`                int(11)                   DEFAULT '0',
    `visualization_view`                    varchar(100)     NOT NULL DEFAULT '',
    `confirm_deselect`                      int(11)                   DEFAULT '1',
    `calcmodel`                             int(10) unsigned          DEFAULT NULL,
    `price_calculation_overrides`           varchar(1024)    NOT NULL DEFAULT '[]',
    `calcmodel_recurring`                   int(10) unsigned          DEFAULT NULL,
    `price_recurring_calculation_overrides` varchar(1024)    NOT NULL DEFAULT '[]',
    `ordering`                              int(11)                   DEFAULT NULL,
    `published`                             int(1) unsigned  NOT NULL,
    `assignment_custom_1`                   varchar(255)     NOT NULL DEFAULT '',
    `assignment_custom_2`                   varchar(255)     NOT NULL DEFAULT '',
    `assignment_custom_3`                   varchar(255)     NOT NULL DEFAULT '',
    `assignment_custom_4`                   varchar(255)     NOT NULL DEFAULT '',
    `rules`                                 text             NOT NULL,
    `option_picker_image`                   varchar(100)     NOT NULL DEFAULT '',
    `calcmodel_weight`                      int(10) unsigned          DEFAULT NULL,
    `shapediver_choice_value`               varchar(512)     NOT NULL DEFAULT '',
    `display_while_disabled`                varchar(16)      NOT NULL DEFAULT 'like_question',
    `internal_name`                         varchar(63)               DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `element_id` (`element_id`),
    KEY `option_id` (`option_id`),
    KEY `calcmodel` (`calcmodel`),
    KEY `calcmodel_recurring` (`calcmodel_recurring`),
    KEY `calcmodel_weight` (`calcmodel_weight`),
    KEY `published` (`published`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_1` FOREIGN KEY (`element_id`) REFERENCES `sltxh_configbox_elements` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_10` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_11` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_12` FOREIGN KEY (`option_id`) REFERENCES `sltxh_configbox_options` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_13` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_14` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_15` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_16` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_17` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_18` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_19` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_2` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_20` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_21` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_22` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_23` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_3` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_4` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_5` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_6` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_7` FOREIGN KEY (`calcmodel`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_8` FOREIGN KEY (`calcmodel_recurring`) REFERENCES `sltxh_configbox_calculations` (`id`),
    CONSTRAINT `sltxh_configbox_xref_element_option_ibfk_9` FOREIGN KEY (`calcmodel_weight`) REFERENCES `sltxh_configbox_calculations` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_xref_group_payment_method`
(
    `payment_id` int(10) unsigned NOT NULL,
    `group_id`   int(10) unsigned NOT NULL,
    PRIMARY KEY (`payment_id`, `group_id`),
    KEY `fk_group_id` (`group_id`),
    CONSTRAINT `sltxh_configbox_xref_group_payment_method_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `sltxh_configbox_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_xref_group_payment_method_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `sltxh_configbox_payment_methods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  ROW_FORMAT = COMPACT;

CREATE TABLE `sltxh_configbox_xref_listing_product`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `listing_id` int(10) unsigned DEFAULT NULL,
    `product_id` int(10) unsigned DEFAULT NULL,
    `ordering`   int(11)          DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `listing_id-product_id` (`listing_id`, `product_id`),
    KEY `product_id` (`product_id`),
    KEY `ordering` (`ordering`),
    CONSTRAINT `sltxh_configbox_xref_listing_product_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `sltxh_configbox_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `sltxh_configbox_xref_listing_product_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `sltxh_configbox_listings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `sltxh_configbox_zones`
(
    `id`    int(10) unsigned NOT NULL AUTO_INCREMENT,
    `label` varchar(100)     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

SET FOREIGN_KEY_CHECKS=1;