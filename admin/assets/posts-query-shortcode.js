/* global tinyMCE */
(function() {
    // defaults
    var templateOpts = {
        wrap_tag: "",
        container: "",
        columns: 4,
        template: ""
    };

    var advancedOpts = {
        status: "publish",
        orderby: "menu_order date",
        order: "desc",
        max: "-1"
    };

    var media = wp.media,
        shortcode_string = queryPosts.shortcode;

    wp.mce = wp.mce || {};

    function getTemplateButton(editor) {
        return {
            type: 'button',
            text: 'Template',
            onclick: function() {
                editor.windowManager.open({
                    title: 'Template options',
                    body: [{
                            type: 'textbox',
                            name: 'wrap_tag',
                            label: 'Тэг контейнера', //'Tag Wrapper',
                            placeholder: 'div',
                            value: templateOpts.wrap_tag
                        },
                        {
                            type: 'textbox',
                            name: 'container',
                            label: 'Класс контейнера', //'Container Class',
                            placeholder: 'true|false|string',
                            value: templateOpts.container
                        },
                        {
                            type: 'textbox',
                            subtype: 'number',
                            name: 'columns',
                            label: 'Столбцов', //'Columns',
                            value: templateOpts.columns || 4
                        },
                        {
                            type: 'textbox',
                            name: 'template',
                            label: 'Необычный дизайн', //'Custom Template',
                            value: templateOpts.template
                        },
                    ],
                    onsubmit: function(e) {
                        templateOpts = e.data;
                    }
                });
            }
        }
    };

    function getAdvancedButton(editor) {
        return {
            type: 'button',
            text: 'Advanced',
            onclick: function() {
                editor.windowManager.open({
                    title: 'Advanced options',
                    body: [{
                            type: 'listbox',
                            name: 'status',
                            label: 'Статус записей', //'Post Status',
                            values: queryPosts.statuses,
                            value: advancedOpts.status
                        },
                        {
                            type: 'listbox',
                            name: 'orderby',
                            label: 'Сортировать по:', // 'Order By',
                            values: queryPosts.orderby,
                            value: advancedOpts.orderby || 'menu_order date'
                        },
                        {
                            type: 'listbox',
                            name: 'order',
                            label: 'Сортировать', //'Order',
                            values: [{
                                    text: 'По убыванию', //'DESC',
                                    value: 'desc'
                                },
                                {
                                    text: 'По возрастанию', //'ASC',
                                    value: 'asc'
                                },
                            ],
                            value: advancedOpts.order
                        },
                        {
                            type: 'textbox',
                            subtype: 'number',
                            name: 'max',
                            label: 'Ограничить количество записей', //'Max Posts',
                            tooltip: '(-1 = без ограничения)',
                            // placeholder: '5',
                            value: advancedOpts.max || -1
                        },
                    ],
                    onsubmit: function(e) {
                        advancedOpts = e.data;
                    }
                });
            }
        }
    };

    wp.mce.query_shortcode = {
        shortcode_data: {},
        getContent: function() {
            // Контент внутри объекта
            return '<p style="text-align: center;">{Simple WP Post Query}</p>';
        },
        edit: function(data) {
            var shortcode_data = wp.shortcode.next(shortcode_string, data);
            var values = shortcode_data.shortcode.attrs.named;

            // values.innercontent = shortcode_data.shortcode.content;
            wp.mce.query_shortcode.popupwindow(tinyMCE.activeEditor, values);
        },
        getBody: function(editor, values, body_type) {
            var body = {};
            switch (body_type) {
                case 'page_childs':
                    body = [{
                        type: 'textbox',
                        name: 'parent',
                        label: 'Старшая страница (Родитель)', //'Parent (for hierarchy)',
                        value: values.parent
                    }];
                    break;

                case 'post_category':
                    body = [{
                            type: 'textbox',
                            name: 'cat',
                            label: 'Categories ID (for post)',
                            placeholder: '6,12,18',
                            value: values.cat
                        },
                        {
                            type: 'textbox',
                            name: 'slug',
                            label: 'Category SLUG (for post)',
                            placeholder: 'articles',
                            value: values.slug
                        }
                    ];
                    break;

                case 'tax_terms':
                    body = [{
                            type: 'textbox',
                            name: 'tax',
                            label: 'Таксаномия', //'taxonomy',
                            value: values.tax
                        },
                        {
                            type: 'textbox',
                            name: 'terms',
                            label: 'Термины таксаномий', //'Terms of tax',
                            value: values.terms
                        }
                    ];
                    break;

                case 'posts_id':
                    body = [{
                        type: 'textbox',
                        name: 'id',
                        label: 'ID записей через запятую',
                        placeholder: '8,10,32',
                        value: values.id
                    }];
                    break;

                case 'post_type':
                default:
                    body = [{
                        type: 'listbox',
                        name: 'type',
                        label: 'Тип записи',
                        values: queryPosts.types,
                        value: values.type,
                    }];
                    break;
            }

            return body;
        },
        popupwindow: function(editor, values, body_type) {
            values = values || [];
            if (typeof onsubmit_callback !== 'function') {
                onsubmit_callback = function(e) {
                    // Insert content when the window form is submitted
                    var args = {
                        tag: shortcode_string,
                        type: 'single',
                        attrs: {
                            type: e.data.type,
                            columns: templateOpts.columns,
                            max: advancedOpts.max
                        }
                    };

                    // defaults
                    if (e.data.id) args.attrs.id = e.data.id;

                    if (e.data.cat) args.attrs.cat = e.data.cat;
                    if (e.data.slug) args.attrs.slug = e.data.slug;

                    if (e.data.parent) args.attrs.parent = e.data.parent;

                    if (e.data.tax) args.attrs.tax = e.data.tax;
                    if (e.data.terms) args.attrs.terms = e.data.terms;

                    if (templateOpts.wrap_tag) args.attrs.wrap_tag = templateOpts.wrap_tag;
                    if (templateOpts.container) args.attrs.container = templateOpts.container;
                    if (templateOpts.template) args.attrs.template = templateOpts.template;

                    if (advancedOpts.status && advancedOpts.status != 'publish') args.attrs.status = advancedOpts.status;
                    if (advancedOpts.order && advancedOpts.order != 'desc') args.attrs.order = advancedOpts.order;
                    if (advancedOpts.orderby && advancedOpts.orderby != 'menu_order date') args.attrs.orderby = advancedOpts.orderby;

                    editor.insertContent(wp.shortcode.string(args));
                };
            }

            var body = this.getBody(editor, values, body_type);
            body.push(getTemplateButton(editor));
            body.push(getAdvancedButton(editor));
            var main = editor.windowManager.open({
                title: 'Simple WordPress Post Query',
                body: body,
                onsubmit: onsubmit_callback
            });
        }
    };

    wp.mce.views.register(shortcode_string, wp.mce.query_shortcode);

}());