(function (window, $) {
    if (!$ || !$.fn || !$.fn.dataTable) {
        return;
    }

    var decoratedTables = [];

    function syncColumnVisibilityButtons(table) {
        setTimeout(function () {
            var $columnVisibilityButtons = $('div.dt-button-collection .buttons-columnVisibility, div.dt-button-collection .dt-button[data-cv-idx]');
            var $collection = $columnVisibilityButtons.closest('div.dt-button-collection');

            $collection.addClass('dt-column-visibility-collection');

            $columnVisibilityButtons.each(function () {
                var $button = $(this);
                var columnIndex = $button.attr('data-cv-idx');
                var isVisible = $button.hasClass('active') || $button.hasClass('dt-button-active');

                if (columnIndex !== undefined) {
                    isVisible = table.column(columnIndex).visible();
                }

                $button
                    .toggleClass('active dt-button-active', isVisible)
                    .toggleClass('dt-column-hidden', !isVisible)
                    .attr('aria-pressed', isVisible ? 'true' : 'false');
            });

            $collection.each(function () {
                var $menu = $(this);
                var menuOffset = $menu.offset();
                var menuWidth = $menu.outerWidth();
                var windowWidth = $(window).width();
                var overflow = menuOffset.left + menuWidth - windowWidth + 12;

                if (overflow > 0) {
                    $menu.css('left', Math.max(12, menuOffset.left - overflow));
                }
            });
        }, 0);
    }

    window.decorateColumnVisibilityButtons = function (dataTable) {
        var table = dataTable && dataTable.settings ? dataTable : new $.fn.dataTable.Api(dataTable);
        var tableNode = table.table().node();

        if ($.inArray(tableNode, decoratedTables) !== -1) {
            return table;
        }

        decoratedTables.push(tableNode);

        table.on('buttons-action.dtColumnVisibility column-visibility.dtColumnVisibility draw.dtColumnVisibility', function () {
            syncColumnVisibilityButtons(table);
        });

        return table;
    };

    $(document).on('init.dt', function (event, settings) {
        window.decorateColumnVisibilityButtons(new $.fn.dataTable.Api(settings));
    });
})(window, window.jQuery);

$(document).ready(function () {

    var handleCheckboxes = function (html, rowIndex, colIndex, cellNode) {
        var $cellNode = $(cellNode);
        var $check = $cellNode.find(':checked');
        return ($check.length) ? ($check.val() == 1 ? 'Yes' : 'No') : $cellNode.text();
    };

    var activeSub = $(document).find('.active-sub');
    if (activeSub.length > 0) {
        activeSub.parent().show();
        activeSub.parent().parent().find('.arrow').addClass('open');
        activeSub.parent().parent().addClass('open');
    }

    $(document).on('click','.dataTable input[type=checkbox]',function () {
        $(this).parents('tr').toggleClass('selected');
    })
    window.dtDefaultOptions = {
        retrieve: true,
        dom: 'lfBrtip<"actions">',
        columnDefs: [],
        "iDisplayLength": 10,
        "aaSorting": [],
        buttons: [
            // {
            //     extend: 'copy',
            //     exportOptions: {
            //         columns: ':visible',
            //         format: {
            //             body: handleCheckboxes
            //         }
            //     }
            // },
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: handleCheckboxes
                    }
                }
            },
            // {
            //     extend: 'excel',
            //     exportOptions: {
            //         columns: ':visible',
            //         format: {
            //             body: handleCheckboxes
            //         }
            //     }
            // },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: handleCheckboxes
                    }
                }
            },
            // {
            //     extend: 'print',
            //     exportOptions: {
            //         columns: ':visible',
            //         format: {
            //             body: handleCheckboxes
            //         }
            //     }
            // },
            'colvis'
        ]
    };
    $('.datatable, .dataTable').each(function () {
        if ($(this).hasClass('dt-select')) {
            window.dtDefaultOptions.select = {
                style: 'multi',
                selector: 'td:first-child'
            };

            window.dtDefaultOptions.columnDefs.push({
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            });
        }
        $(this).dataTable(window.dtDefaultOptions);
    });
    if (typeof window.route_mass_crud_entries_destroy != 'undefined') {
        console.log($('.datatable, .ajaxTable, .dataTable').siblings('.actions'))
    }

    $(document).on('click', '.js-delete-selected', function (e) {
        if (confirm('Are you sure?')) {
            e.preventDefault();

            var ids = [];

            $(this).closest('.actions').siblings('.datatable,.dataTable, .ajaxTable').find('tbody tr.selected').each(function () {
                ids.push($(this).data('entry-id'));
            });

            $.ajax({
                method: 'POST',
                url: $(this).attr('href'),
                data: {
                    _token: _token,
                    ids: ids
                }
            }).done(function () {
                location.reload();
            });
        }

        return false;
    });

    $(document).on('click', '#select-all', function () {
        var selected = $(this).is(':checked');
        $(this).closest('table.datatable, table.dataTable, table.ajaxTable').find('td:first-child').each(function () {
            if (selected != $(this).closest('tr').hasClass('selected')) {
                $(this).click();
            }
        });
    });

    $('.mass').click(function () {
        if ($(this).is(":checked")) {
            $('.single').each(function () {
                if ($(this).is(":checked") == false) {
                    $(this).click();
                }
            });
        } else {
            $('.single').each(function () {
                if ($(this).is(":checked") == true) {
                    $(this).click();
                }
            });
        }
    });

    $('.page-sidebar').on('click', 'li > a', function (e) {

        if ($('body').hasClass('page-sidebar-closed') && $(this).parent('li').parent('.page-sidebar-menu').size() === 1) {
            return;
        }

        var hasSubMenu = $(this).next().hasClass('sub-menu');

        if ($(this).next().hasClass('sub-menu always-open')) {
            return;
        }

        var parent = $(this).parent().parent();
        var the = $(this);
        var menu = $('.page-sidebar-menu');
        var sub = $(this).next();

        var autoScroll = menu.data("auto-scroll");
        var slideSpeed = parseInt(menu.data("slide-speed"));
        var keepExpand = menu.data("keep-expanded");

        if (keepExpand !== true) {
            parent.children('li.open').children('a').children('.arrow').removeClass('open');
            parent.children('li.open').children('.sub-menu:not(.always-open)').slideUp(slideSpeed);
            parent.children('li.open').removeClass('open');
        }

        var slideOffeset = -200;

        if (sub.is(":visible")) {
            $('.arrow', $(this)).removeClass("open");
            $(this).parent().removeClass("open");
            sub.slideUp(slideSpeed, function () {
                if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
                    if ($('body').hasClass('page-sidebar-fixed')) {
                        menu.slimScroll({
                            'scrollTo': (the.position()).top
                        });
                    }
                }
            });
        } else if (hasSubMenu) {
            $('.arrow', $(this)).addClass("open");
            $(this).parent().addClass("open");
            sub.slideDown(slideSpeed, function () {
                if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
                    if ($('body').hasClass('page-sidebar-fixed')) {
                        menu.slimScroll({
                            'scrollTo': (the.position()).top
                        });
                    }
                }
            });
        }
        if (hasSubMenu == true || $(this).attr('href') == '#') {
            e.preventDefault();
        }
    });

    $('.select2').select2();

});

function processAjaxTables() {
    $('.ajaxTable').each(function () {
        window.dtDefaultOptions.processing = true;
        window.dtDefaultOptions.serverSide = true;
        if ($(this).hasClass('dt-select')) {
            window.dtDefaultOptions.select = {
                style: 'multi',
                selector: 'td:first-child'
            };

            window.dtDefaultOptions.columnDefs.push({
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            });
        }
        $(this).DataTable(window.dtDefaultOptions);
        if (typeof window.route_mass_crud_entries_destroy != 'undefined') {
            $(this).siblings('.actions').html('<a href="' + window.route_mass_crud_entries_destroy + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
        }
    });

}
