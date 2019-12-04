/* global tinymce, queryPosts */
(function() {
    tinymce.PluginManager.add('query_shortcode', function(editor, url) {
        editor.addButton('query_shortcode', {
            type: 'menubutton',
            text: '{Query Posts}',
            menu: [{
                    text: 'Запрос по типу записи',
                    onclick: function() {
                        wp.mce.query_shortcode.popupwindow(editor, null, 'post_type');
                    }
                },
                {
                    text: 'Запрос по категории Записи',
                    onclick: function() {
                        wp.mce.query_shortcode.popupwindow(editor, null, 'post_category');
                    }
                },
                {
                    text: 'Запрос по родительской странице',
                    onclick: function() {
                        wp.mce.query_shortcode.popupwindow(editor, null, 'page_childs');
                    }
                },
                {
                    text: 'Запрос по терминам таксаномии',
                    onclick: function() {
                        wp.mce.query_shortcode.popupwindow(editor, null, 'tax_terms');
                    }
                },
                {
                    text: 'По ИД записей',
                    onclick: function() {
                        wp.mce.query_shortcode.popupwindow(editor, null, 'posts_id');
                    }
                },
            ]
        });
    });
})();