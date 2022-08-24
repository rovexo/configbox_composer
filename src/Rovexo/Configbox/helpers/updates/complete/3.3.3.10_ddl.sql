
create table if not exists sltxh_cbcheckout_order_records
(
    id                  int unsigned auto_increment
        primary key,
    user_id             int unsigned                  null,
    delivery_id         int unsigned                  null,
    payment_id          int unsigned                  null,
    cart_id             int unsigned                  null,
    store_id            int unsigned   default 0      not null,
    created_on          datetime                      null comment 'UTC',
    paid                int            default 0      null,
    paid_on             datetime                      null comment 'UTC',
    status              int unsigned   default 0      null,
    invoice_released    varchar(1)     default '0'    not null,
    comment             text                          null,
    custom_1            text                          not null,
    custom_2            text                          not null,
    custom_3            text                          not null,
    custom_4            text                          not null,
    coupon_discount_net decimal(20, 4) default 0.0000 not null,
    transaction_id      text                          null,
    transaction_data    text                          null,
    custom_5            text                          not null,
    custom_6            text                          not null,
    custom_7            text                          not null,
    custom_8            text                          not null,
    custom_9            text                          not null,
    custom_10           text                          not null,
    ga_client_id        varchar(255)   default ''     null
)
    collate = utf8_unicode_ci;

create table if not exists sltxh_cbcheckout_order_cities
(
    id        int unsigned             not null,
    order_id  int unsigned             not null,
    city_name varchar(200) default ''  not null,
    county_id int unsigned             null,
    custom_1  text                     not null,
    custom_2  text                     not null,
    custom_3  text                     not null,
    custom_4  text                     not null,
    ordering  int                      null,
    published varchar(1)   default '1' not null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_cities_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index order_id
    on sltxh_cbcheckout_order_cities (order_id);

create index state_id
    on sltxh_cbcheckout_order_cities (county_id);

create table if not exists sltxh_cbcheckout_order_counties
(
    id          int unsigned             not null,
    order_id    int unsigned             not null,
    county_name varchar(200) default ''  not null,
    state_id    int unsigned             null,
    custom_1    text                     not null,
    custom_2    text                     not null,
    custom_3    text                     not null,
    custom_4    text                     not null,
    ordering    int                      null,
    published   varchar(1)   default '1' not null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_counties_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index order_id
    on sltxh_cbcheckout_order_counties (order_id);

create index state_id
    on sltxh_cbcheckout_order_counties (state_id);

create table if not exists sltxh_cbcheckout_order_countries
(
    id             int unsigned           not null,
    order_id       int unsigned           not null,
    country_name   varchar(64)            null,
    country_3_code varchar(3)             null,
    country_2_code varchar(2)             null,
    vat_free       int        default 1   null,
    in_eu_vat_area varchar(1) default '0' not null,
    published      int        default 1   null,
    ordering       int                    null,
    custom_1       text                   not null,
    custom_2       text                   not null,
    custom_3       text                   not null,
    custom_4       text                   not null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_countries_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index idx_country_name
    on sltxh_cbcheckout_order_countries (country_name);

create index in_eu_vat_area
    on sltxh_cbcheckout_order_countries (in_eu_vat_area);

create index order_id
    on sltxh_cbcheckout_order_countries (order_id);

create table if not exists sltxh_cbcheckout_order_currencies
(
    id            int unsigned                            not null,
    order_id      int unsigned                            not null,
    base          int                     default 0       null,
    multiplicator decimal(10, 5) unsigned default 1.00000 not null,
    symbol        varchar(10)             default ''      not null,
    code          varchar(10)             default ''      not null,
    `default`     int                     default 0       null,
    ordering      int                     default 0       null,
    published     int                     default 1       null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_currencies_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index code
    on sltxh_cbcheckout_order_currencies (code);

create index order_id
    on sltxh_cbcheckout_order_currencies (order_id);

create table if not exists sltxh_cbcheckout_order_invoices
(
    id                    int auto_increment
        primary key,
    invoice_number_prefix varchar(10)  default ''  not null,
    invoice_number_serial int unsigned             not null,
    order_id              int unsigned             null,
    file                  varchar(100)             not null,
    released_by           int          default 0   not null,
    released_on           datetime                 null comment 'UTC',
    changed               varchar(1)   default '0' not null,
    original_file         varchar(100) default ''  not null,
    changed_by            int unsigned default 0   not null,
    changed_on            datetime                 null comment 'UTC',
    constraint order_id
        unique (order_id),
    constraint unique_prefix_serial
        unique (invoice_number_prefix, invoice_number_serial),
    constraint sltxh_cbcheckout_order_invoices_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
)
    collate = utf8_unicode_ci;

create table if not exists sltxh_cbcheckout_order_payment_methods
(
    order_id       int unsigned                  not null,
    id             int unsigned                  not null,
    price          decimal(20, 4) default 0.0000 not null,
    taxclass_id    int unsigned   default 0      null,
    params         text                          null,
    ordering       int            default 0      null,
    percentage     decimal(20, 3) default 0.000  not null,
    price_min      decimal(20, 4) default 0.0000 not null,
    price_max      decimal(20, 4) default 0.0000 not null,
    connector_name varchar(50)    default ''     not null,
    primary key (order_id, id),
    constraint sltxh_cbcheckout_order_payment_methods_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index index_price
    on sltxh_cbcheckout_order_payment_methods (price);

create index index_price_max
    on sltxh_cbcheckout_order_payment_methods (price_max);

create index index_price_min
    on sltxh_cbcheckout_order_payment_methods (price_min);

create table if not exists sltxh_cbcheckout_order_payment_trackings
(
    id          int unsigned auto_increment
        primary key,
    user_id     int unsigned           not null,
    order_id    int unsigned           null,
    got_tracked varchar(1) default '0' not null,
    constraint sltxh_cbcheckout_order_payment_trackings_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index order_id
    on sltxh_cbcheckout_order_payment_trackings (order_id);

create index user_id
    on sltxh_cbcheckout_order_payment_trackings (user_id, order_id);

create table if not exists sltxh_cbcheckout_order_positions
(
    id                                     int unsigned auto_increment
        primary key,
    order_id                               int unsigned                           null,
    product_id                             int unsigned                           null,
    product_sku                            varchar(255)            default ''     not null,
    product_image                          varchar(50)             default ''     not null,
    quantity                               int unsigned            default 1      null,
    weight                                 decimal(20, 4) unsigned default 0.0000 not null,
    taxclass_id                            int unsigned                           null,
    taxclass_recurring_id                  int unsigned                           null,
    product_base_price_net                 decimal(20, 4) unsigned default 0.0000 not null,
    product_base_price_overrides           varchar(1024)           default '[]'   not null,
    product_base_price_recurring_net       decimal(20, 4) unsigned default 0.0000 not null,
    product_base_price_recurring_overrides varchar(1024)           default '[]'   not null,
    price_net                              decimal(20, 4) unsigned default 0.0000 not null,
    price_recurring_net                    decimal(20, 4) unsigned default 0.0000 not null,
    dispatch_time                          int unsigned            default 0      null,
    product_custom_1                       text                                   not null,
    product_custom_2                       text                                   not null,
    product_custom_3                       text                                   not null,
    product_custom_4                       text                                   not null,
    constraint sltxh_cbcheckout_order_positions_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
)
    collate = utf8_unicode_ci;

create table if not exists sltxh_cbcheckout_order_configurations
(
    id                        int unsigned auto_increment
        primary key,
    position_id               int unsigned                  null,
    price_net                 decimal(20, 4) default 0.0000 not null,
    price_overrides           varchar(1024)  default '[]'   not null,
    price_recurring_net       decimal(20, 4) default 0.0000 not null,
    price_recurring_overrides varchar(1024)  default '[]'   not null,
    element_id                int unsigned                  null,
    element_type              varchar(30)                   not null,
    weight                    decimal(20, 4) default 0.0000  not null,
    xref_id                   int unsigned                  null,
    option_id                 int unsigned                  null,
    value                     text                          not null,
    output_value              text                          not null,
    element_code              varchar(255)   default ''     not null,
    option_sku                varchar(255)   default ''     not null,
    option_image              varchar(50)    default ''     not null,
    show_in_overviews         int unsigned   default 1      null,
    element_custom_1          text                          not null,
    element_custom_2          text                          not null,
    element_custom_3          text                          not null,
    element_custom_4          text                          not null,
    assignment_custom_1       text                          not null,
    assignment_custom_2       text                          not null,
    assignment_custom_3       text                          not null,
    assignment_custom_4       text                          not null,
    option_custom_1           text                          not null,
    option_custom_2           text                          not null,
    option_custom_3           text                          not null,
    option_custom_4           text                          not null,
    constraint sltxh_cbcheckout_order_configurations_ibfk_1
        foreign key (position_id) references sltxh_cbcheckout_order_positions (id)
            on update cascade
)
    collate = utf8_unicode_ci;

create index element_id
    on sltxh_cbcheckout_order_configurations (element_id);

create index position_id
    on sltxh_cbcheckout_order_configurations (position_id);

create index xref_id
    on sltxh_cbcheckout_order_configurations (xref_id);

create index order_id
    on sltxh_cbcheckout_order_positions (order_id);

create index product_id
    on sltxh_cbcheckout_order_positions (product_id);

create table if not exists sltxh_cbcheckout_order_quotations
(
    order_id   int unsigned auto_increment
        primary key,
    created_on datetime                null comment 'UTC',
    created_by int unsigned default 0  not null,
    file       varchar(50)  default '' not null,
    constraint sltxh_cbcheckout_order_quotations_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
)
    collate = utf8_unicode_ci;

create index cart_id
    on sltxh_cbcheckout_order_records (cart_id);

create index user_id
    on sltxh_cbcheckout_order_records (user_id);

create table if not exists sltxh_cbcheckout_order_salutations
(
    id       int unsigned           not null,
    order_id int unsigned           not null,
    gender   varchar(1) default '1' not null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_salutations_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index order_id
    on sltxh_cbcheckout_order_salutations (order_id);

create table if not exists sltxh_cbcheckout_order_shipping_methods
(
    order_id     int unsigned                           not null,
    id           int unsigned                           not null,
    shipper_id   int unsigned                           null,
    zone_id      int unsigned                           null,
    minweight    decimal(20, 4) unsigned default 0.0000 not null,
    maxweight    decimal(20, 4) unsigned default 0.0000 not null,
    deliverytime int                                    null,
    price        decimal(20, 4)          default 0.0000 not null,
    taxclass_id  int unsigned                           null,
    external_id  varchar(100)            default ''     not null,
    ordering     int                     default 0      null,
    primary key (order_id, id),
    constraint sltxh_cbcheckout_order_shipping_methods_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index maxweight
    on sltxh_cbcheckout_order_shipping_methods (maxweight);

create index minweight
    on sltxh_cbcheckout_order_shipping_methods (minweight);

create index ordering
    on sltxh_cbcheckout_order_shipping_methods (ordering);

create index price
    on sltxh_cbcheckout_order_shipping_methods (price);

create table if not exists sltxh_cbcheckout_order_states
(
    id          int unsigned            not null,
    order_id    int unsigned            not null,
    country_id  int unsigned            null,
    name        varchar(50)  default '' not null,
    iso_code    varchar(50)  default '' not null,
    fips_number varchar(5)   default '' not null,
    custom_1    text                    not null,
    custom_2    text                    not null,
    custom_3    text                    not null,
    custom_4    text                    not null,
    ordering    int          default 0  null,
    published   int unsigned default 1  null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_states_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create index country_id
    on sltxh_cbcheckout_order_states (country_id);

create index iso_fips
    on sltxh_cbcheckout_order_states (iso_code, fips_number);

create index order_id
    on sltxh_cbcheckout_order_states (order_id);

create index ordering
    on sltxh_cbcheckout_order_states (ordering, published);

create table if not exists sltxh_cbcheckout_order_strings
(
    order_id     int unsigned                                   not null,
    `table`      varchar(50) collate utf8_unicode_ci default '' not null,
    type         int unsigned                                   not null,
    `key`        bigint unsigned                                not null,
    language_tag varchar(5)                          default '' not null,
    text         text                                           not null,
    primary key (order_id, `table`, type, `key`, language_tag),
    constraint sltxh_cbcheckout_order_strings_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create table if not exists sltxh_cbcheckout_order_tax_class_rates
(
    order_id         int unsigned            null,
    tax_class_id     int unsigned            null,
    city_id          int unsigned default 0  not null,
    county_id        int unsigned default 0  not null,
    zone_id          int unsigned            null,
    state_id         int unsigned            null,
    country_id       int unsigned            not null,
    tax_rate         decimal(4, 2) unsigned  not null,
    default_tax_rate decimal(10, 3) unsigned not null,
    tax_code         varchar(100) default '' not null,
    constraint unique_all
        unique (order_id, tax_class_id, city_id, county_id, state_id, country_id),
    constraint sltxh_cbcheckout_order_tax_class_rates_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
);

create table if not exists sltxh_cbcheckout_order_user_groups
(
    order_id                    int unsigned                                 not null,
    group_id                    int unsigned                                 not null,
    discount_start_1            decimal(20, 4) unsigned default 0.0000       not null,
    discount_start_2            decimal(20, 4) unsigned default 0.0000       not null,
    discount_start_3            decimal(20, 4) unsigned default 0.0000       not null,
    discount_start_4            decimal(20, 4) unsigned default 0.0000       not null,
    discount_start_5            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_1           decimal(20, 4)          default 0.0000       not null,
    discount_factor_2           decimal(20, 4)          default 0.0000       not null,
    discount_factor_3           decimal(20, 4)          default 0.0000       not null,
    discount_factor_4           decimal(20, 4)          default 0.0000       not null,
    discount_factor_5           decimal(20, 4)          default 0.0000       not null,
    discount_amount_1           decimal(20, 4)          default 0.0000       not null,
    discount_amount_2           decimal(20, 4)          default 0.0000       not null,
    discount_amount_3           decimal(20, 4)          default 0.0000       not null,
    discount_amount_4           decimal(20, 4)          default 0.0000       not null,
    discount_amount_5           decimal(20, 4)          default 0.0000       not null,
    discount_type_1             varchar(32)             default 'percentage' not null,
    discount_type_2             varchar(32)             default 'percentage' not null,
    discount_type_3             varchar(32)             default 'percentage' not null,
    discount_type_4             varchar(32)             default 'percentage' not null,
    discount_type_5             varchar(32)             default 'percentage' not null,
    discount_recurring_start_1  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_2  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_3  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_4  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_5  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_1 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_2 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_3 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_4 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_5 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_1 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_2 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_3 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_4 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_5 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_type_1   varchar(32)             default 'percentage' not null,
    discount_recurring_type_2   varchar(32)             default 'percentage' not null,
    discount_recurring_type_3   varchar(32)             default 'percentage' not null,
    discount_recurring_type_4   varchar(32)             default 'percentage' not null,
    discount_recurring_type_5   varchar(32)             default 'percentage' not null,
    title                       varchar(255)            default ''           not null,
    custom_1                    text                                         not null,
    custom_2                    text                                         not null,
    custom_3                    text                                         not null,
    custom_4                    text                                         not null,
    enable_checkout_order       int unsigned            default 1            null,
    enable_see_pricing          int unsigned            default 1            null,
    enable_save_order           int unsigned            default 1            null,
    enable_request_quotation    int unsigned            default 1            null,
    b2b_mode                    int unsigned            default 0            null,
    joomla_user_group_id        int unsigned            default 0            null,
    quotation_download          varchar(1)              default '1'          not null,
    quotation_email             varchar(1)              default '1'          not null,
    primary key (order_id, group_id),
    constraint sltxh_cbcheckout_order_user_groups_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
            on update cascade
)
    collate = utf8_unicode_ci;

create table if not exists sltxh_cbcheckout_order_users
(
    id                   int unsigned             not null,
    order_id             int unsigned             not null,
    companyname          varchar(255)             not null,
    gender               varchar(1)   default '1' not null,
    firstname            varchar(255)             not null,
    lastname             varchar(255)             not null,
    address1             varchar(255)             not null,
    address2             varchar(255)             not null,
    zipcode              varchar(15)              not null,
    city                 varchar(255)             not null,
    country              int unsigned             null,
    email                varchar(255)             not null,
    phone                varchar(255)             not null,
    billingcompanyname   varchar(255)             not null,
    billingfirstname     varchar(255)             not null,
    billinglastname      varchar(255)             not null,
    billinggender        varchar(1)               null,
    billingaddress1      varchar(255)             not null,
    billingaddress2      varchar(255)             not null,
    billingzipcode       varchar(15)              not null,
    billingcity          varchar(255)             not null,
    billingcountry       int unsigned             null,
    billingemail         varchar(255)             not null,
    billingphone         varchar(255)             not null,
    samedelivery         int          default 1   null,
    created              datetime                 null comment 'UTC',
    vatin                varchar(200) default ''  not null,
    group_id             int unsigned default 0   null,
    newsletter           int unsigned default 0   null,
    platform_user_id     int unsigned             null,
    salutation_id        int unsigned             null,
    billingsalutation_id int unsigned             null,
    state                int unsigned             null,
    billingstate         int unsigned             null,
    custom_1             text                     not null,
    custom_2             text                     not null,
    custom_3             text                     not null,
    custom_4             text                     not null,
    language_tag         varchar(5)               null,
    county_id            int unsigned             null,
    billingcounty_id     int unsigned             null,
    city_id              int unsigned             null,
    billingcity_id       int unsigned             null,
    primary key (id, order_id),
    constraint sltxh_cbcheckout_order_users_ibfk_1
        foreign key (order_id) references sltxh_cbcheckout_order_records (id)
);

create index billingcountry
    on sltxh_cbcheckout_order_users (billingcountry);

create index billingsalutation_id
    on sltxh_cbcheckout_order_users (billingsalutation_id);

create index billingstate
    on sltxh_cbcheckout_order_users (billingstate);

create index country
    on sltxh_cbcheckout_order_users (country);

create index group_id
    on sltxh_cbcheckout_order_users (group_id);

create index language_tag
    on sltxh_cbcheckout_order_users (language_tag);

create index order_id
    on sltxh_cbcheckout_order_users (order_id);

create index salutation_id
    on sltxh_cbcheckout_order_users (salutation_id);

create index state
    on sltxh_cbcheckout_order_users (state);

create table if not exists sltxh_configbox_active_languages
(
    tag varchar(5) not null,
    primary key (tag)
);

create table if not exists sltxh_configbox_connectors
(
    id           int unsigned auto_increment
        primary key,
    name         varchar(100)           not null,
    ordering     int                    null,
    published    int unsigned default 1 null,
    after_system int unsigned default 1 null,
    file         varchar(500)           not null
);

create index ordering
    on sltxh_configbox_connectors (ordering);

create index published
    on sltxh_configbox_connectors (published);

create table if not exists sltxh_configbox_countries
(
    id             int unsigned auto_increment
        primary key,
    country_name   varchar(64)            null,
    country_3_code varchar(3)             null,
    country_2_code varchar(2)             null,
    vat_free       varchar(1) default '1' not null,
    in_eu_vat_area varchar(1) default '0' not null,
    published      varchar(1) default '1' not null,
    ordering       int        default 0   null,
    custom_1       text                   not null,
    custom_2       text                   not null,
    custom_3       text                   not null,
    custom_4       text                   not null
);

create index country_2_code
    on sltxh_configbox_countries (country_2_code);

create index idx_country_name
    on sltxh_configbox_countries (country_name);

create index in_eu_vat_area
    on sltxh_configbox_countries (in_eu_vat_area);

create index ordering
    on sltxh_configbox_countries (ordering);

create index published
    on sltxh_configbox_countries (published);

create index vat_free
    on sltxh_configbox_countries (vat_free);

create table if not exists sltxh_configbox_currencies
(
    id            int unsigned auto_increment
        primary key,
    base          int                     default 0       null,
    multiplicator decimal(10, 5) unsigned default 1.00000 not null,
    symbol        varchar(10)                             not null,
    code          varchar(10)                             not null,
    `default`     int                     default 0       null,
    ordering      int                                     null,
    published     int                                     null,
    constraint code
        unique (code)
);

create index ordering
    on sltxh_configbox_currencies (ordering);

create index published
    on sltxh_configbox_currencies (published);

create table if not exists sltxh_configbox_examples
(
    id         int unsigned auto_increment
        primary key,
    product_id int unsigned not null,
    published  int unsigned null,
    ordering   int          not null
);

create table if not exists sltxh_configbox_groups
(
    id                          int unsigned auto_increment
        primary key,
    title                       varchar(255)                                 not null,
    discount_start_1            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_1           decimal(20, 4)          default 0.0000       not null,
    discount_start_2            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_2           decimal(20, 4)          default 0.0000       not null,
    discount_start_3            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_3           decimal(20, 4)          default 0.0000       not null,
    discount_start_4            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_4           decimal(20, 4)          default 0.0000       not null,
    discount_start_5            decimal(20, 4) unsigned default 0.0000       not null,
    discount_factor_5           decimal(20, 4)          default 0.0000       not null,
    custom_1                    text                                         not null,
    custom_2                    text                                         not null,
    custom_3                    text                                         not null,
    custom_4                    text                                         not null,
    enable_checkout_order       varchar(1)              default '1'          not null,
    enable_see_pricing          varchar(1)              default '1'          not null,
    enable_save_order           varchar(1)              default '1'          not null,
    enable_request_quotation    varchar(1)              default '1'          not null,
    b2b_mode                    varchar(1)              default '1'          not null,
    joomla_user_group_id        int unsigned            default 0            null,
    quotation_download          varchar(1)              default '1'          not null,
    quotation_email             varchar(1)              default '1'          not null,
    discount_amount_1           decimal(20, 4)          default 0.0000       not null,
    discount_amount_2           decimal(20, 4)          default 0.0000       not null,
    discount_amount_3           decimal(20, 4)          default 0.0000       not null,
    discount_amount_4           decimal(20, 4)          default 0.0000       not null,
    discount_amount_5           decimal(20, 4)          default 0.0000       not null,
    discount_type_1             varchar(32)             default 'percentage' not null,
    discount_type_2             varchar(32)             default 'percentage' not null,
    discount_type_3             varchar(32)             default 'percentage' not null,
    discount_type_4             varchar(32)             default 'percentage' not null,
    discount_type_5             varchar(32)             default 'percentage' not null,
    discount_recurring_start_1  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_2  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_3  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_4  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_start_5  decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_1 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_2 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_3 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_4 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_factor_5 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_1 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_2 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_3 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_4 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_amount_5 decimal(20, 4)          default 0.0000       not null,
    discount_recurring_type_1   varchar(32)             default 'percentage' not null,
    discount_recurring_type_2   varchar(32)             default 'percentage' not null,
    discount_recurring_type_3   varchar(32)             default 'percentage' not null,
    discount_recurring_type_4   varchar(32)             default 'percentage' not null,
    discount_recurring_type_5   varchar(32)             default 'percentage' not null
);

create index joomla_user_group_id
    on sltxh_configbox_groups (joomla_user_group_id);

create table if not exists sltxh_configbox_listings
(
    id              int unsigned auto_increment
        primary key,
    layoutname      varchar(128) default 'default' not null,
    published       varchar(1)   default '1'       not null,
    product_sorting varchar(1)   default '0'       not null
);

create table if not exists sltxh_configbox_config
(
    id                                   int                                               not null,
    lastcleanup                          bigint unsigned default 0                         null,
    usertime                             int unsigned    default 24                        null,
    unorderedtime                        int unsigned    default 24                        null,
    intervals                            int unsigned    default 12                        null,
    labelexpiry                          int unsigned    default 28                        null,
    securecheckout                       varchar(1)      default '0'                       not null,
    weightunits                          varchar(16)     default ''                        null,
    defaultprodimage                     varchar(32)     default ''                        null,
    product_key                          varchar(64)     default ''                        null,
    license_manager_satellites           text                                              null,
    page_nav_cart_button_last_page_only  varchar(1)      default '0'                       not null,
    page_nav_block_on_missing_selections varchar(1)      default '0'                       not null,
    label_element_custom_1               text                                              null,
    label_element_custom_2               text                                              null,
    label_element_custom_3               text                                              null,
    label_element_custom_4               text                                              null,
    label_element_custom_translatable_1  text                                              null,
    label_element_custom_translatable_2  text                                              null,
    label_assignment_custom_1            text                                              null,
    label_assignment_custom_2            text                                              null,
    label_assignment_custom_3            text                                              null,
    label_assignment_custom_4            text                                              null,
    label_option_custom_1                text                                              null,
    label_option_custom_2                text                                              null,
    label_option_custom_3                text                                              null,
    label_option_custom_4                text                                              null,
    use_internal_question_names          varchar(1)      default '0'                       not null,
    enable_geolocation                   varchar(1)      default '0'                       not null,
    geolocation_type                     varchar(32)     default 'maxmind_geoip2_db'       not null,
    maxmind_license_key                  varchar(64)     default ''                        not null,
    maxmind_user_id                      varchar(32)     default ''                        null,
    pm_show_delivery_options             int unsigned    default 0                         null,
    pm_show_payment_options              int unsigned    default 0                         null,
    label_product_custom_1               text                                              null,
    label_product_custom_2               text                                              null,
    label_product_custom_3               text                                              null,
    label_product_custom_4               text                                              null,
    label_product_custom_5               text                                              null,
    label_product_custom_6               text                                              null,
    enable_reviews_products              varchar(1)      default '0'                       not null,
    continue_listing_id                  int unsigned                                      null,
    enable_performance_tracking          varchar(1)      default '0'                       not null,
    pm_show_regular_first                varchar(1)      default '1'                       not null,
    pm_regular_show_overview             varchar(1)      default '1'                       not null,
    pm_regular_show_prices               varchar(1)      default '1'                       not null,
    pm_regular_show_categories           varchar(1)      default '1'                       not null,
    pm_regular_show_elements             varchar(1)      default '1'                       not null,
    pm_regular_show_elementprices        varchar(1)      default '1'                       not null,
    pm_regular_expand_categories         varchar(1)      default '2'                       not null,
    pm_recurring_show_overview           varchar(1)      default '1'                       not null,
    pm_recurring_show_prices             varchar(1)      default '1'                       not null,
    pm_recurring_show_categories         varchar(1)      default '1'                       not null,
    pm_recurring_show_elements           varchar(1)      default '1'                       not null,
    pm_recurring_show_elementprices      varchar(1)      default '1'                       not null,
    pm_recurring_expand_categories       varchar(1)      default '2'                       not null,
    show_conversion_table                varchar(1)      default '0'                       not null,
    page_nav_show_tabs                   varchar(1)      default '0'                       not null,
    label_option_custom_5                text                                              null,
    label_option_custom_6                text                                              null,
    language_tag                         varchar(5)                                        null,
    pm_regular_show_taxes                varchar(1)      default '0'                       not null,
    pm_regular_show_cart_button          varchar(1)      default '0'                       not null,
    pm_recurring_show_taxes              varchar(1)      default '0'                       not null,
    pm_recurring_show_cart_button        varchar(1)      default '0'                       not null,
    pm_show_net_in_b2c                   varchar(1)      default '0'                       not null,
    review_notification_email            varchar(255)    default ''                        not null,
    default_customer_group_id            int unsigned                                      null,
    default_country_id                   int unsigned                                      null,
    disable_delivery                     varchar(1)      default '0'                       not null,
    sku_in_order_record                  varchar(1)      default '0'                       not null,
    newsletter_preset                    varchar(1)      default '0'                       not null,
    alternate_shipping_preset            varchar(1)      default '0'                       not null,
    show_recurring_login_cart            varchar(1)      default '0'                       not null,
    explicit_agreement_terms             varchar(1)      default '0'                       not null,
    explicit_agreement_rp                varchar(1)      default '0'                       not null,
    enable_invoicing                     varchar(1)      default '0'                       not null,
    send_invoice                         varchar(1)      default '0'                       not null,
    invoice_generation                   varchar(1)      default '0'                       not null,
    invoice_number_prefix                varchar(32)     default ''                        not null,
    invoice_number_start                 int unsigned    default 1                         not null,
    page_nav_show_buttons                varchar(1)      default '1'                       not null,
    structureddata                       varchar(1)      default '1'                       not null,
    structureddata_in                    varchar(16)     default 'configurator'            not null,
    use_ga_ecommerce                     varchar(1)      default '0'                       not null,
    ga_property_id                       varchar(64)     default ''                        null,
    use_ga_enhanced_ecommerce            varchar(1)      default '0'                       not null,
    ga_behavior_offline_psps             varchar(32)     default 'conversion_when_ordered' not null,
    use_internal_answer_names            varchar(1)      default '0'                       not null,
    use_minified_js                      varchar(1)      default '1'                       not null,
    use_minified_css                     varchar(1)      default '1'                       not null,
    use_assets_cache_buster              varchar(1)      default '1'                       not null,
    primary key (id),
    constraint sltxh_configbox_config_ibfk_1
        foreign key (default_customer_group_id) references sltxh_configbox_groups (id),
    constraint sltxh_configbox_config_ibfk_2
        foreign key (default_country_id) references sltxh_configbox_countries (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_config_ibfk_3
        foreign key (continue_listing_id) references sltxh_configbox_listings (id)
            on update cascade on delete set null
);

create index continue_listing_id
    on sltxh_configbox_config (continue_listing_id);

create index default_country_id
    on sltxh_configbox_config (default_country_id);

create index default_customer_group_id
    on sltxh_configbox_config (default_customer_group_id);

create index published
    on sltxh_configbox_listings (published);

create table if not exists sltxh_configbox_magento_xref_mprod_cbprod
(
    id                 int unsigned auto_increment
        primary key,
    cb_product_id      int unsigned default 0 not null comment 'CB Product Id',
    magento_product_id int unsigned default 0 not null comment 'Magento Product ID'
);

create table if not exists sltxh_configbox_notifications
(
    id            int unsigned auto_increment
        primary key,
    name          varchar(40)   not null,
    type          varchar(50)   not null,
    statuscode    int           null,
    send_customer int default 1 null,
    send_manager  int default 1 null
);

create index statuscode
    on sltxh_configbox_notifications (statuscode);

create table if not exists sltxh_configbox_oldlabels
(
    id           int unsigned auto_increment
        primary key,
    type         int unsigned    not null,
    `key`        int unsigned    null,
    label        varchar(255)    not null,
    prod_id      int unsigned    null,
    created      bigint unsigned not null,
    language_tag varchar(5)      not null,
    constraint uniqe_strings
        unique (type, `key`, language_tag)
);

create index created
    on sltxh_configbox_oldlabels (created);

create index prod_id
    on sltxh_configbox_oldlabels (prod_id);

create table if not exists sltxh_configbox_options
(
    id                        int unsigned auto_increment
        primary key,
    sku                       varchar(60)    default ''        not null,
    price                     decimal(20, 4) default 0.0000    not null,
    price_overrides           varchar(1024)  default '[]'      not null,
    price_recurring           decimal(20, 4) default 0.0000    not null,
    price_recurring_overrides varchar(1024)  default '[]'      not null,
    weight                    decimal(20, 4) default 0.0000    not null,
    option_custom_1           varchar(255)   default ''        not null,
    option_custom_2           varchar(255)   default ''        not null,
    option_custom_3           varchar(255)   default ''        not null,
    option_custom_4           varchar(255)   default ''        not null,
    available                 varchar(1)     default '0'       not null,
    availibility_date         date                             null comment 'UTC',
    option_image              varchar(200)   default ''        not null,
    was_price                 decimal(20, 4) default 0.0000    not null,
    was_price_recurring       decimal(20, 4) default 0.0000    not null,
    disable_non_available     varchar(1)     default '0'       not null,
    desc_display_method       varchar(16)    default 'tooltip' not null
);

create index sku
    on sltxh_configbox_options (sku);

create table if not exists sltxh_configbox_salutations
(
    id     int unsigned auto_increment
        primary key,
    gender varchar(1) default '1' not null
);

create table if not exists sltxh_configbox_session
(
    id         varchar(128)            not null,
    user_agent varchar(200) default '' not null,
    ip_address varchar(100) default '' not null,
    data       text                    not null,
    updated    bigint unsigned         not null,
    primary key (id)
);

create index updated
    on sltxh_configbox_session (updated);

create table if not exists sltxh_configbox_shippers
(
    id        int unsigned auto_increment
        primary key,
    published varchar(1) default '0' not null
);

create index published
    on sltxh_configbox_shippers (published);

create table if not exists sltxh_configbox_states
(
    id          int unsigned auto_increment
        primary key,
    country_id  int unsigned             null,
    name        varchar(50)  default ''  not null,
    iso_code    varchar(50)  default ''  not null,
    fips_number varchar(5)   default ''  not null,
    custom_1    varchar(255) default ''  null,
    custom_2    varchar(255) default ''  null,
    custom_3    varchar(255) default ''  null,
    custom_4    varchar(255) default ''  null,
    ordering    int          default 0   null,
    published   varchar(1)   default '1' not null,
    constraint sltxh_configbox_states_ibfk_1
        foreign key (country_id) references sltxh_configbox_countries (id)
);

create table if not exists sltxh_configbox_counties
(
    id          int unsigned auto_increment
        primary key,
    county_name varchar(200) default ''  not null,
    state_id    int unsigned             null,
    custom_1    text                     not null,
    custom_2    text                     not null,
    custom_3    text                     not null,
    custom_4    text                     not null,
    ordering    int                      null,
    published   varchar(1)   default '1' not null,
    constraint sltxh_configbox_counties_ibfk_1
        foreign key (state_id) references sltxh_configbox_states (id)
);

create table if not exists sltxh_configbox_cities
(
    id        int unsigned auto_increment
        primary key,
    city_name varchar(200) default ''  not null,
    county_id int unsigned             null,
    custom_1  text                     not null,
    custom_2  text                     not null,
    custom_3  text                     not null,
    custom_4  text                     not null,
    ordering  int                      null,
    published varchar(1)   default '1' not null,
    constraint sltxh_configbox_cities_ibfk_1
        foreign key (county_id) references sltxh_configbox_counties (id)
);

create index state_id
    on sltxh_configbox_cities (county_id);

create index state_id
    on sltxh_configbox_counties (state_id);

create table if not exists sltxh_configbox_shopdata
(
    id                    int unsigned not null,
    shopname              varchar(200) not null,
    shoplogo              varchar(100) not null,
    shopaddress1          varchar(200) not null,
    shopaddress2          varchar(200) not null,
    shopzipcode           varchar(40)  not null,
    shopcity              varchar(100) not null,
    country_id            int unsigned null,
    state_id              int unsigned null,
    shopphonesales        varchar(100) not null,
    shopphonesupport      varchar(100) not null,
    shopemailsales        varchar(100) not null,
    shopemailsupport      varchar(100) not null,
    shopfax               varchar(100) not null,
    shopbankname          varchar(100) not null,
    shopbankaccountholder varchar(100) not null,
    shopbankaccount       varchar(100) not null,
    shopbankcode          varchar(100) not null,
    shopbic               varchar(100) not null,
    shopiban              varchar(100) not null,
    shopuid               varchar(100) not null,
    shopcomreg            varchar(100) not null,
    shopwebsite           varchar(255) null,
    shopowner             varchar(255) null,
    shoplegalvenue        varchar(255) null,
    primary key (id),
    constraint sltxh_configbox_shopdata_ibfk_1
        foreign key (country_id) references sltxh_configbox_countries (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_shopdata_ibfk_2
        foreign key (state_id) references sltxh_configbox_states (id)
            on update cascade on delete set null
);

create index country_id
    on sltxh_configbox_shopdata (country_id);

create index state_id
    on sltxh_configbox_shopdata (state_id);

create index country_id
    on sltxh_configbox_states (country_id);

create index iso_fips
    on sltxh_configbox_states (iso_code, fips_number);

create index ordering
    on sltxh_configbox_states (ordering, published);

create index published
    on sltxh_configbox_states (published);

create table if not exists sltxh_configbox_strings
(
    type         int unsigned not null,
    `key`        int unsigned not null comment 'Primary key value for the regarding record.',
    language_tag varchar(5)   not null,
    text         text         not null,
    constraint uniqe_strings
        unique (type, `key`, language_tag)
);

create table if not exists sltxh_configbox_system_vars
(
    `key` varchar(128) not null,
    value text         not null,
    primary key (`key`)
);

create table if not exists sltxh_configbox_tax_classes
(
    id               int unsigned auto_increment
        primary key,
    title            varchar(255)            not null,
    default_tax_rate decimal(4, 2) unsigned  not null,
    id_external      varchar(100) default '' not null
);

create table if not exists sltxh_configbox_payment_methods
(
    id             int unsigned auto_increment
        primary key,
    price          decimal(20, 4) default 0.0000 not null,
    taxclass_id    int unsigned                  null,
    params         text                          null,
    ordering       int            default 0      null,
    published      varchar(1)     default '0'    not null,
    percentage     decimal(20, 4) default 0.0000 not null,
    price_min      decimal(20, 4) default 0.0000 not null,
    price_max      decimal(20, 4) default 0.0000 not null,
    connector_name varchar(50)    default ''     not null,
    constraint sltxh_configbox_payment_methods_ibfk_1
        foreign key (taxclass_id) references sltxh_configbox_tax_classes (id)
);

create index ordering
    on sltxh_configbox_payment_methods (ordering);

create index published
    on sltxh_configbox_payment_methods (published);

create index taxclass_id
    on sltxh_configbox_payment_methods (taxclass_id);

create table if not exists sltxh_configbox_products
(
    id                                         int unsigned auto_increment
        primary key,
    sku                                        varchar(60)                            not null,
    prod_image                                 varchar(50)                            not null,
    baseimage                                  varchar(100)                           not null,
    opt_image_x                                int unsigned                           null,
    opt_image_y                                int unsigned                           null,
    baseprice                                  decimal(20, 4) unsigned default 0.0000 not null,
    baseprice_recurring                        decimal(20, 4) unsigned default 0.0000 not null,
    baseprice_overrides                        varchar(1024)           default '[]'   not null,
    baseprice_recurring_overrides              varchar(1024)           default '[]'   not null,
    baseweight                                 decimal(20, 4) unsigned default 0.0000 not null,
    taxclass_id                                int unsigned                           null,
    taxclass_recurring_id                      int unsigned                           null,
    layoutname                                 varchar(100)                           not null,
    published                                  varchar(1)              default '0'    not null,
    pm_show_delivery_options                   varchar(1)              default '2'    not null,
    pm_show_payment_options                    varchar(1)              default '2'    not null,
    product_custom_1                           text                                   not null,
    product_custom_2                           text                                   not null,
    product_custom_3                           text                                   not null,
    product_custom_4                           text                                   not null,
    enable_reviews                             varchar(1)              default '2'    not null,
    external_reviews_id                        varchar(200)            default ''     not null,
    dispatch_time                              int unsigned                           null,
    pm_show_regular_first                      int                     default 2      null,
    pm_regular_show_overview                   int                     default 2      null,
    pm_regular_show_prices                     int                     default 2      null,
    pm_regular_show_categories                 int                     default 2      null,
    pm_regular_show_elements                   int                     default 2      null,
    pm_regular_show_elementprices              int                     default 2      null,
    pm_regular_expand_categories               int                     default 2      null,
    pm_recurring_show_overview                 int                     default 2      null,
    pm_recurring_show_prices                   int                     default 2      null,
    pm_recurring_show_categories               int                     default 2      null,
    pm_recurring_show_elements                 int                     default 2      null,
    pm_recurring_show_elementprices            int                     default 2      null,
    pm_recurring_expand_categories             int                     default 3      null,
    page_nav_show_tabs                         varchar(1)              default '2'    not null,
    show_buy_button                            varchar(1)              default '1'    not null,
    was_price                                  decimal(20, 4)          default 0.0000 not null,
    was_price_recurring                        decimal(20, 4)          default 0.0000 not null,
    pm_show_net_in_b2c                         varchar(1)              default '0'    not null,
    pm_regular_show_taxes                      varchar(1)              default '2'    not null,
    pm_regular_show_cart_button                varchar(1)              default '2'    not null,
    pm_recurring_show_taxes                    varchar(1)              default '2'    not null,
    pm_recurring_show_cart_button              varchar(1)              default '2'    not null,
    product_detail_panes_method                varchar(16)             default 'tabs' not null,
    product_detail_panes_in_listings           varchar(1)              default '0'    not null,
    product_detail_panes_in_product_pages      varchar(1)              default '1'    not null,
    product_detail_panes_in_configurator_steps varchar(1)              default '0'    not null,
    page_nav_show_buttons                      varchar(1)              default '2'    not null,
    page_nav_block_on_missing_selections       varchar(1)              default '2'    not null,
    page_nav_cart_button_last_page_only        varchar(1)              default '2'    not null,
    visualization_type                         varchar(16)             default 'none' not null,
    shapediver_model_data                      text                                   not null,
    use_recurring_pricing                      varchar(1)              default '0'    not null,
    show_product_details_button                varchar(1)              default '0'    not null,
    product_details_page_type                  varchar(16)             default 'none' not null,
    constraint sltxh_configbox_products_ibfk_1
        foreign key (taxclass_id) references sltxh_configbox_tax_classes (id),
    constraint sltxh_configbox_products_ibfk_2
        foreign key (taxclass_recurring_id) references sltxh_configbox_tax_classes (id)
);

create table if not exists sltxh_configbox_calculations
(
    id         int unsigned auto_increment
        primary key,
    name       varchar(200) not null,
    type       varchar(10)  not null,
    product_id int unsigned null,
    constraint sltxh_configbox_calculations_ibfk_1
        foreign key (product_id) references sltxh_configbox_products (id)
            on update cascade on delete set null
);

create table if not exists sltxh_configbox_calculation_formulas
(
    id   int unsigned not null,
    calc text         not null,
    primary key (id),
    constraint sltxh_configbox_calculation_formulas_ibfk_1
        foreign key (id) references sltxh_configbox_calculations (id)
            on update cascade on delete cascade
);

create table if not exists sltxh_configbox_calculation_matrices_data
(
    id       int unsigned                  not null,
    x        bigint                        not null,
    y        bigint                        not null,
    value    decimal(20, 4) default 0.0000 not null,
    ordering int unsigned                  null,
    primary key (id, x, y),
    constraint sltxh_configbox_calculation_matrices_data_ibfk_1
        foreign key (id) references sltxh_configbox_calculations (id)
            on update cascade on delete cascade
);

create index ordering
    on sltxh_configbox_calculation_matrices_data (ordering);

create index product_id
    on sltxh_configbox_calculations (product_id);

create table if not exists sltxh_configbox_pages
(
    id                 int unsigned auto_increment
        primary key,
    visualization_view varchar(100) default ''  not null,
    layoutname         varchar(100)             not null,
    published          varchar(1)   default '0' not null,
    ordering           int          default 0   null,
    css_classes        varchar(255) default ''  not null,
    product_id         int unsigned             null,
    constraint sltxh_configbox_pages_ibfk_1
        foreign key (product_id) references sltxh_configbox_products (id)
);

create table if not exists sltxh_configbox_elements
(
    id                           int unsigned auto_increment
        primary key,
    page_id                      int unsigned                                      null,
    el_image                     varchar(100)                                      not null,
    required                     int                                               null,
    validate                     int                                               null,
    minval                       varchar(255)                                      null,
    maxval                       varchar(255)                                      null,
    calcmodel                    int unsigned                                      null,
    calcmodel_recurring          int unsigned                                      null,
    multiplicator                float                                             not null,
    published                    int                                               null,
    ordering                     int                                               null,
    asproducttitle               int unsigned   default 0                          null,
    default_value                text                                              not null,
    show_in_overview             int            default 1                          null,
    text_calcmodel               int            default 0                          null,
    element_custom_1             varchar(255)   default ''                         not null,
    element_custom_2             varchar(255)   default ''                         not null,
    element_custom_3             varchar(255)   default ''                         not null,
    element_custom_4             varchar(255)   default ''                         not null,
    rules                        text                                              not null,
    internal_name                varchar(255)   default ''                         not null,
    element_css_classes          varchar(100)   default ''                         not null,
    calcmodel_id_min_val         int unsigned                                      null,
    calcmodel_id_max_val         int unsigned                                      null,
    upload_extensions            varchar(255)   default 'png, jpg, jpeg, gif, tif' not null,
    upload_mime_types            varchar(255)   default ''                         not null,
    upload_size_mb               float unsigned default '1'                        not null,
    slider_steps                 float unsigned default '1'                        not null,
    calcmodel_weight             int unsigned                                      null,
    choices                      text                                              not null,
    desc_display_method          varchar(1)     default '1'                        not null,
    behavior_on_activation       varchar(16)    default 'none'                     not null,
    behavior_on_changes          varchar(16)    default 'silent'                   not null,
    question_type                varchar(64)    default ''                         not null,
    prefill_on_init              varchar(1)     default '0'                        not null,
    input_restriction            varchar(16)    default 'plaintext'                not null,
    set_min_value                varchar(16)    default 'none'                     not null,
    set_max_value                varchar(16)    default 'none'                     not null,
    show_unit                    varchar(1)     default '0'                        not null,
    title_display                varchar(16)    default 'heading'                  not null,
    is_shapediver_control        varchar(1)     default '0'                        not null,
    shapediver_parameter_id      varchar(255)   default ''                         not null,
    behavior_on_inconsistency    varchar(32)    default 'deselect'                 not null,
    display_while_disabled       varchar(16)    default 'hide'                     not null,
    calendar_validation_type_min varchar(16)    default 'none'                     null,
    calendar_validation_type_max varchar(16)    default 'none'                     null,
    calendar_days_min            int                                               null,
    calendar_days_max            int                                               null,
    calendar_first_day           varchar(10)    default 'locale'                   null,
    constraint sltxh_configbox_elements_ibfk_1
        foreign key (page_id) references sltxh_configbox_pages (id),
    constraint sltxh_configbox_elements_ibfk_2
        foreign key (calcmodel_id_min_val) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_elements_ibfk_3
        foreign key (calcmodel_id_max_val) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_elements_ibfk_4
        foreign key (calcmodel) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_elements_ibfk_5
        foreign key (calcmodel_recurring) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_elements_ibfk_6
        foreign key (calcmodel_weight) references sltxh_configbox_calculations (id)
            on update cascade on delete set null
);

create table if not exists sltxh_configbox_calculation_codes
(
    id           int unsigned auto_increment
        primary key,
    element_id_a int unsigned null,
    element_id_b int unsigned null,
    element_id_c int unsigned null,
    element_id_d int unsigned null,
    code         text         not null,
    constraint sltxh_configbox_calculation_codes_ibfk_1
        foreign key (id) references sltxh_configbox_calculations (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_calculation_codes_ibfk_2
        foreign key (element_id_a) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_codes_ibfk_3
        foreign key (element_id_b) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_codes_ibfk_4
        foreign key (element_id_c) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_codes_ibfk_5
        foreign key (element_id_d) references sltxh_configbox_elements (id)
);

create index element_id_a
    on sltxh_configbox_calculation_codes (element_id_a);

create index element_id_b
    on sltxh_configbox_calculation_codes (element_id_b);

create index element_id_c
    on sltxh_configbox_calculation_codes (element_id_c);

create index element_id_d
    on sltxh_configbox_calculation_codes (element_id_d);

create table if not exists sltxh_configbox_calculation_matrices
(
    id                 int unsigned auto_increment
        primary key,
    column_element_id  int unsigned                   null,
    row_element_id     int unsigned                   null,
    round              int unsigned                   null,
    lookup_value       int            default 0       null,
    multiplicator      decimal(20, 5) default 0.00000 not null,
    multielementid     int unsigned                   null,
    column_calc_id     int unsigned                   null,
    row_calc_id        int unsigned                   null,
    calcmodel_id_multi int unsigned                   null,
    row_type           varchar(16)    default 'none'  not null,
    column_type        varchar(16)    default 'none'  not null,
    constraint sltxh_configbox_calculation_matrices_ibfk_1
        foreign key (id) references sltxh_configbox_calculations (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_calculation_matrices_ibfk_2
        foreign key (column_element_id) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_matrices_ibfk_3
        foreign key (row_element_id) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_matrices_ibfk_4
        foreign key (multielementid) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_calculation_matrices_ibfk_5
        foreign key (column_calc_id) references sltxh_configbox_calculations (id),
    constraint sltxh_configbox_calculation_matrices_ibfk_6
        foreign key (row_calc_id) references sltxh_configbox_calculations (id),
    constraint sltxh_configbox_calculation_matrices_ibfk_7
        foreign key (calcmodel_id_multi) references sltxh_configbox_calculations (id)
);

create index calcmodel_id_multi
    on sltxh_configbox_calculation_matrices (calcmodel_id_multi);

create index column_calc_id
    on sltxh_configbox_calculation_matrices (column_calc_id);

create index column_element_id
    on sltxh_configbox_calculation_matrices (column_element_id);

create index multielementid
    on sltxh_configbox_calculation_matrices (multielementid);

create index row_calc_id
    on sltxh_configbox_calculation_matrices (row_calc_id);

create index row_element_id
    on sltxh_configbox_calculation_matrices (row_element_id);

create index calcmodel
    on sltxh_configbox_elements (calcmodel);

create index calcmodel_id_max_val
    on sltxh_configbox_elements (calcmodel_id_max_val);

create index calcmodel_id_min_val
    on sltxh_configbox_elements (calcmodel_id_min_val);

create index calcmodel_recurring
    on sltxh_configbox_elements (calcmodel_recurring);

create index calcmodel_weight
    on sltxh_configbox_elements (calcmodel_weight);

create index is_shapediver_control
    on sltxh_configbox_elements (is_shapediver_control);

create index ordering
    on sltxh_configbox_elements (ordering);

create index `page_id-ordering`
    on sltxh_configbox_elements (page_id, ordering);

create index published
    on sltxh_configbox_elements (published);

create index ordering
    on sltxh_configbox_pages (ordering);

create index product_id
    on sltxh_configbox_pages (product_id);

create index published
    on sltxh_configbox_pages (published);

create table if not exists sltxh_configbox_product_detail_panes
(
    id                    int unsigned auto_increment
        primary key,
    product_id            int unsigned            not null,
    heading_icon_filename varchar(30)             not null,
    css_classes           varchar(255) default '' not null,
    ordering              int                     null,
    constraint sltxh_configbox_product_detail_panes_ibfk_1
        foreign key (product_id) references sltxh_configbox_products (id)
);

create index ordering
    on sltxh_configbox_product_detail_panes (ordering);

create index product_id
    on sltxh_configbox_product_detail_panes (product_id);

create index published
    on sltxh_configbox_products (published);

create index taxclass_id
    on sltxh_configbox_products (taxclass_id);

create index taxclass_recurring_id
    on sltxh_configbox_products (taxclass_recurring_id);

create table if not exists sltxh_configbox_reviews
(
    id           int unsigned auto_increment
        primary key,
    `name`       varchar(100)           not null,
    rating       decimal(2, 1)          not null,
    `comment`    text                   not null,
    published    varchar(1) default '0' not null,
    language_tag varchar(5)             not null,
    date_created datetime               null comment 'UTC',
    product_id   int unsigned           null,
    constraint sltxh_configbox_reviews_ibfk_1
        foreign key (product_id) references sltxh_configbox_products (id)
            on update cascade on delete cascade
);

create index date_created
    on sltxh_configbox_reviews (date_created);

create index product_id
    on sltxh_configbox_reviews (product_id);

create index published
    on sltxh_configbox_reviews (published, language_tag);

create table if not exists sltxh_configbox_tax_class_rates
(
    tax_class_id int unsigned                        null,
    city_id      int unsigned                        null,
    county_id    int unsigned                        null,
    state_id     int unsigned                        null,
    country_id   int unsigned                        null,
    tax_rate     decimal(4, 2) unsigned default 0.00 not null,
    tax_code     varchar(100)           default ''   not null,
    constraint unique_all
        unique (tax_class_id, city_id, county_id, state_id, country_id),
    constraint sltxh_configbox_tax_class_rates_ibfk_1
        foreign key (tax_class_id) references sltxh_configbox_tax_classes (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_tax_class_rates_ibfk_2
        foreign key (city_id) references sltxh_configbox_cities (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_tax_class_rates_ibfk_3
        foreign key (county_id) references sltxh_configbox_counties (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_tax_class_rates_ibfk_4
        foreign key (state_id) references sltxh_configbox_states (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_tax_class_rates_ibfk_5
        foreign key (country_id) references sltxh_configbox_countries (id)
            on update cascade on delete cascade
);

create index city_id
    on sltxh_configbox_tax_class_rates (city_id);

create index country_id
    on sltxh_configbox_tax_class_rates (country_id);

create index county_id
    on sltxh_configbox_tax_class_rates (county_id);

create index state_id
    on sltxh_configbox_tax_class_rates (state_id);

create table if not exists sltxh_configbox_user_field_definitions
(
    id                 int unsigned auto_increment
        primary key,
    field_name         varchar(30)              not null,
    show_checkout      varchar(1)   default '0' not null,
    require_checkout   varchar(1)   default '0' not null,
    show_quotation     varchar(1)   default '0' not null,
    require_quotation  varchar(1)   default '0' not null,
    show_saveorder     varchar(1)   default '0' not null,
    require_saveorder  varchar(1)   default '0' not null,
    show_profile       varchar(1)   default '0' not null,
    require_profile    varchar(1)   default '0' not null,
    validation_browser varchar(255) default ''  not null,
    validation_server  varchar(255) default ''  not null
);

create table if not exists sltxh_configbox_users
(
    id                   int unsigned auto_increment
        primary key,
    platform_user_id     int unsigned             null,
    companyname          varchar(255)             not null,
    gender               varchar(1)   default '1' not null,
    firstname            varchar(255)             not null,
    lastname             varchar(255)             not null,
    address1             varchar(255)             not null,
    address2             varchar(255)             not null,
    zipcode              varchar(15)              not null,
    city                 varchar(255)             not null,
    country              int unsigned             null,
    email                varchar(255)             not null,
    phone                varchar(255)             not null,
    billingcompanyname   varchar(255)             not null,
    billingfirstname     varchar(255)             not null,
    billinglastname      varchar(255)             not null,
    billinggender        varchar(1)               null,
    billingaddress1      varchar(255)             not null,
    billingaddress2      varchar(255)             not null,
    billingzipcode       varchar(15)              not null,
    billingcity          varchar(255)             not null,
    billingcountry       int unsigned             null,
    billingemail         varchar(255)             not null,
    billingphone         varchar(255)             not null,
    samedelivery         int          default 1   null,
    created              datetime                 null comment 'UTC',
    password             varchar(255)             null,
    vatin                varchar(200)             not null,
    group_id             int unsigned             null,
    newsletter           int unsigned default 0   null,
    is_temporary         int unsigned default 0   null,
    salutation_id        int unsigned             null,
    billingsalutation_id int unsigned             null,
    state                int unsigned             null,
    billingstate         int unsigned             null,
    custom_1             varchar(255) default ''  null,
    custom_2             varchar(255) default ''  null,
    custom_3             varchar(255) default ''  null,
    custom_4             varchar(255) default ''  null,
    language_tag         varchar(5)               null,
    county_id            int unsigned             null,
    billingcounty_id     int unsigned             null,
    city_id              int unsigned             null,
    billingcity_id       int unsigned             null,
    constraint sltxh_configbox_users_ibfk_1
        foreign key (group_id) references sltxh_configbox_groups (id)
            on update cascade on delete set null
);

create table if not exists sltxh_configbox_carts
(
    id           int unsigned auto_increment
        primary key,
    user_id      int unsigned not null,
    created_time datetime     null comment 'UTC',
    constraint sltxh_configbox_carts_ibfk_1
        foreign key (user_id) references sltxh_configbox_users (id)
            on update cascade on delete cascade
);

create table if not exists sltxh_configbox_cart_positions
(
    id       int unsigned auto_increment
        primary key,
    cart_id  int unsigned           not null,
    prod_id  int unsigned           null,
    quantity int unsigned default 1 null,
    created  datetime               null comment 'UTC',
    finished int unsigned default 0 not null,
    constraint sltxh_configbox_cart_positions_ibfk_1
        foreign key (cart_id) references sltxh_configbox_carts (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_cart_positions_ibfk_2
        foreign key (prod_id) references sltxh_configbox_products (id)
            on update cascade on delete cascade
);

create table if not exists sltxh_configbox_cart_position_configurations
(
    id               int unsigned auto_increment
        primary key,
    cart_position_id int unsigned  not null,
    prod_id          int unsigned  null,
    element_id       int unsigned  null,
    selection        varchar(2000) not null,
    constraint sltxh_configbox_cart_position_configurations_ibfk_1
        foreign key (cart_position_id) references sltxh_configbox_cart_positions (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_cart_position_configurations_ibfk_2
        foreign key (prod_id) references sltxh_configbox_products (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_cart_position_configurations_ibfk_3
        foreign key (element_id) references sltxh_configbox_elements (id)
            on update cascade on delete cascade
);

create index cart_position_id
    on sltxh_configbox_cart_position_configurations (cart_position_id);

create index element_id
    on sltxh_configbox_cart_position_configurations (element_id);

create index prod_id
    on sltxh_configbox_cart_position_configurations (prod_id);

create index cart_id
    on sltxh_configbox_cart_positions (cart_id);

create index created
    on sltxh_configbox_cart_positions (created);

create index finished
    on sltxh_configbox_cart_positions (finished);

create index prod_id
    on sltxh_configbox_cart_positions (prod_id);

create index created_time
    on sltxh_configbox_carts (created_time);

create index user_id
    on sltxh_configbox_carts (user_id);

create index billingcity_id
    on sltxh_configbox_users (billingcity_id);

create index billingcountry
    on sltxh_configbox_users (billingcountry);

create index billingcounty_id
    on sltxh_configbox_users (billingcounty_id);

create index billingsalutation_id
    on sltxh_configbox_users (billingsalutation_id);

create index billingstate
    on sltxh_configbox_users (billingstate);

create index city_id
    on sltxh_configbox_users (city_id);

create index country
    on sltxh_configbox_users (country);

create index county_id
    on sltxh_configbox_users (county_id);

create index created
    on sltxh_configbox_users (created);

create index group_id
    on sltxh_configbox_users (group_id);

create index is_temporary
    on sltxh_configbox_users (is_temporary);

create index language_tag
    on sltxh_configbox_users (language_tag);

create index platform_user_id
    on sltxh_configbox_users (platform_user_id);

create index salutation_id
    on sltxh_configbox_users (salutation_id);

create index state
    on sltxh_configbox_users (state);

create table if not exists sltxh_configbox_xref_country_payment_method
(
    payment_id int unsigned not null,
    country_id int unsigned not null,
    primary key (country_id, payment_id),
    constraint sltxh_configbox_xref_country_payment_method_ibfk_1
        foreign key (country_id) references sltxh_configbox_countries (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_xref_country_payment_method_ibfk_2
        foreign key (payment_id) references sltxh_configbox_payment_methods (id)
            on update cascade on delete cascade
);

create index payment_id
    on sltxh_configbox_xref_country_payment_method (payment_id);

create table if not exists sltxh_configbox_xref_element_option
(
    id                                    int unsigned auto_increment
        primary key,
    element_id                            int unsigned                          null,
    option_id                             int unsigned                          null,
    `default`                             int unsigned                          not null,
    visualization_image                   varchar(100)  default ''              not null,
    visualization_stacking                int           default 0               null,
    visualization_view                    varchar(100)  default ''              not null,
    confirm_deselect                      int           default 1               null,
    calcmodel                             int unsigned                          null,
    price_calculation_overrides           varchar(1024) default '[]'            not null,
    calcmodel_recurring                   int unsigned                          null,
    price_recurring_calculation_overrides varchar(1024) default '[]'            not null,
    ordering                              int                                   null,
    published                             int unsigned                          not null,
    assignment_custom_1                   varchar(255)  default ''              not null,
    assignment_custom_2                   varchar(255)  default ''              not null,
    assignment_custom_3                   varchar(255)  default ''              not null,
    assignment_custom_4                   varchar(255)  default ''              not null,
    rules                                 text                                  not null,
    option_picker_image                   varchar(100)  default ''              not null,
    calcmodel_weight                      int unsigned                          null,
    shapediver_choice_value               varchar(512)  default ''              not null,
    display_while_disabled                varchar(16)   default 'like_question' not null,
    internal_name                         varchar(63)   default ''              null,
    constraint sltxh_configbox_xref_element_option_ibfk_1
        foreign key (element_id) references sltxh_configbox_elements (id),
    constraint sltxh_configbox_xref_element_option_ibfk_2
        foreign key (option_id) references sltxh_configbox_options (id),
    constraint sltxh_configbox_xref_element_option_ibfk_3
        foreign key (calcmodel) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_xref_element_option_ibfk_4
        foreign key (calcmodel_recurring) references sltxh_configbox_calculations (id)
            on update cascade on delete set null,
    constraint sltxh_configbox_xref_element_option_ibfk_5
        foreign key (calcmodel_weight) references sltxh_configbox_calculations (id)
            on update cascade on delete set null
);

create index calcmodel
    on sltxh_configbox_xref_element_option (calcmodel);

create index calcmodel_recurring
    on sltxh_configbox_xref_element_option (calcmodel_recurring);

create index calcmodel_weight
    on sltxh_configbox_xref_element_option (calcmodel_weight);

create index element_id
    on sltxh_configbox_xref_element_option (element_id);

create index option_id
    on sltxh_configbox_xref_element_option (option_id);

create index ordering
    on sltxh_configbox_xref_element_option (ordering);

create index published
    on sltxh_configbox_xref_element_option (published);

create table if not exists sltxh_configbox_xref_group_payment_method
(
    payment_id int unsigned not null,
    group_id   int unsigned not null,
    primary key (payment_id, group_id),
    constraint sltxh_configbox_xref_group_payment_method_ibfk_1
        foreign key (group_id) references sltxh_configbox_groups (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_xref_group_payment_method_ibfk_2
        foreign key (payment_id) references sltxh_configbox_payment_methods (id)
            on update cascade on delete cascade
);

create index fk_group_id
    on sltxh_configbox_xref_group_payment_method (group_id);

create table if not exists sltxh_configbox_xref_listing_product
(
    id         int unsigned auto_increment
        primary key,
    listing_id int unsigned  null,
    product_id int unsigned  null,
    ordering   int default 0 null,
    constraint `listing_id-product_id`
        unique (listing_id, product_id),
    constraint sltxh_configbox_xref_listing_product_ibfk_1
        foreign key (product_id) references sltxh_configbox_products (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_xref_listing_product_ibfk_2
        foreign key (listing_id) references sltxh_configbox_listings (id)
            on update cascade on delete cascade
);

create index ordering
    on sltxh_configbox_xref_listing_product (ordering);

create index product_id
    on sltxh_configbox_xref_listing_product (product_id);

create table if not exists sltxh_configbox_zones
(
    id    int unsigned auto_increment
        primary key,
    label varchar(100) not null
);

create table if not exists sltxh_configbox_shipping_methods
(
    id           int unsigned auto_increment
        primary key,
    shipper_id   int unsigned                           null,
    zone_id      int unsigned                           null,
    minweight    decimal(20, 4) unsigned default 0.0000 not null,
    maxweight    decimal(20, 4) unsigned default 0.0000 not null,
    deliverytime int unsigned            default 0      null,
    price        decimal(20, 4)          default 0.0000 not null,
    taxclass_id  int unsigned                           null,
    published    varchar(1)              default '0'    not null,
    external_id  varchar(100)            default ''     not null,
    ordering     int                     default 0      null,
    constraint sltxh_configbox_shipping_methods_ibfk_1
        foreign key (shipper_id) references sltxh_configbox_shippers (id),
    constraint sltxh_configbox_shipping_methods_ibfk_2
        foreign key (taxclass_id) references sltxh_configbox_tax_classes (id),
    constraint sltxh_configbox_shipping_methods_ibfk_3
        foreign key (zone_id) references sltxh_configbox_zones (id)
);

create index external_id
    on sltxh_configbox_shipping_methods (external_id);

create index ordering
    on sltxh_configbox_shipping_methods (ordering);

create index published
    on sltxh_configbox_shipping_methods (published);

create index shipper_id
    on sltxh_configbox_shipping_methods (shipper_id);

create index taxclass_id
    on sltxh_configbox_shipping_methods (taxclass_id);

create index zone_id
    on sltxh_configbox_shipping_methods (zone_id);

create table if not exists sltxh_configbox_xref_country_zone
(
    zone_id    int unsigned not null,
    country_id int unsigned not null,
    primary key (country_id, zone_id),
    constraint sltxh_configbox_xref_country_zone_ibfk_1
        foreign key (zone_id) references sltxh_configbox_zones (id)
            on update cascade on delete cascade,
    constraint sltxh_configbox_xref_country_zone_ibfk_2
        foreign key (country_id) references sltxh_configbox_countries (id)
            on update cascade on delete cascade
);

create index zone_id
    on sltxh_configbox_xref_country_zone (zone_id);

