jQuery(document).ready(function () {

    init();

    /* Add and remove rule rows */
    jQuery(".addRow").on("click", addRow);
    jQuery(".removeRow").on("click", function () {
        var parentForm = jQuery(this).closest("[data-row-parent=true]"),
            className = parentForm.find(".addRow").data("clone-class"),
            row = jQuery(this).closest(className);
        removeRow(row, parentForm, className);
    });

    function init() {
        jQuery(".addRow").each(function () {
            var parentClass = jQuery(this).data("parent-class"),
                parent = jQuery(this).closest(parentClass).attr("data-row-parent", true);
        });
    }

    function addRow() {
        var parentClass = jQuery(this).data("parent-class"),
            parentForm = jQuery(this).closest(parentClass ? parentClass : "form"),
            className = jQuery(this).data("clone-class"),
            firstRow = parentForm.find(className).first(),
            htmlFirstRow = firstRow.wrap("</p>").parent().html();
        firstRow.unwrap();

        var rows = parentForm.find(className),
            row = jQuery(htmlFirstRow).insertAfter(rows.last()),
            inputs = row.find(":input"),
            remBtnPlace = row.find(".removeBtnPlace");

        remBtnPlace = remBtnPlace.length ? remBtnPlace : row;

        var remBtn = jQuery('<input class="removeRow" type="button" value="X" />').appendTo(remBtnPlace);

        remBtn.on("click", removeRow.bind(remBtn, row, parentForm, className));

        for (var i = 0; i < inputs.length; i++) {
            var name = jQuery(inputs[i]).attr("name");
            var newName = name.split("[")[0] + "[" + (rows.length + 1) + "][" + name.split("[").splice(2).join("[");

            jQuery(inputs[i]).attr("name", newName);
            jQuery(inputs[i]).val("");
        }

        row.find("select").find('option:eq(0)').prop('selected', true);
        if (row.find('select[name*="[trigger]"]').length) {
            row.find('select[name*="[trigger]"]').each(showDivOnOption).change(showDivOnOption);
        }

        parentForm.find(className).each(function (i) {
            if (i % 2 == 0) {
                jQuery(this).addClass("cs_row_odd").removeClass("cs_row_even");
            } else {
                jQuery(this).addClass("cs_row_even").removeClass("cs_row_odd");
            }
            var rowName = jQuery(this).find(".rowName");
            if (rowName.length) {
                var h = rowName.html();
                h = h.split(" ");
                rowName.html(h[0] + " " + (i + 1));
            }
        });
    }
    function removeRow(row, parent, className) {
        row.remove();
        var rows = parent.find(className);

        for (var k = 0; k < rows.length; k++) {
            var inputs = jQuery(rows[k]).find(":input");

            for (var i = 0; i < inputs.length; i++) {
                var name = jQuery(inputs[i]).attr("name");
                if (name) {
                    var newName = name.split("[")[0] + "[" + (k + 1) + "][" + name.split("[").splice(2).join("[");

                    jQuery(inputs[i]).attr("name", newName);
                }
            }
        }
        rows.each(function (i) {
            if (i % 2 == 0) {
                jQuery(this).addClass("cs_row_odd").removeClass("cs_row_even");
            } else {
                jQuery(this).addClass("cs_row_even").removeClass("cs_row_odd");
            }
            var rowName = jQuery(this).find(".rowName");
            if (rowName.length) {
                var h = rowName.html();
                h = h.split(" ");
                rowName.html(h[0] + " " + (i + 1));
            }
        })
    }

    /* Rule show and hide form options */
    jQuery('.form-table.affgs.rules select[name*="[trigger]"]').each(showDivOnOption).change(showDivOnOption).trigger('change');

    function showDivOnOption() {
        var parent = jQuery(this).closest("tbody.gs_rules");
        parent.find("div[id]").hide();

        if (jQuery(this).attr("name").indexOf("[trigger]") > -1) {
            var option = jQuery(this).find(":selected").val();

            if (option.length) {
                parent.find("#" + option).show();
            }
        }
    }
});