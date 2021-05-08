$(document).ready(function () {

    init();
	
	/* Add and remove rule rows */
    $(".addRow").on("click", addRow);
    $(".removeRow").on("click", function () {
        var parentForm = $(this).closest("[data-row-parent=true]"),
            className = parentForm.find(".addRow").data("clone-class"),
            row = $(this).closest(className);
        removeRow(row, parentForm, className);
    });

    function init() {
        $(".addRow").each(function () {
            var parentClass = $(this).data("parent-class"),
                parent = $(this).closest(parentClass).attr("data-row-parent", true);
        });
    }

    function addRow() {
        var parentClass = $(this).data("parent-class"),
            parentForm = $(this).closest(parentClass ? parentClass : "form"),
            className = $(this).data("clone-class"),
            firstRow = parentForm.find(className).first(),
            htmlFirstRow = firstRow.wrap("</p>").parent().html();
        firstRow.unwrap();

        var rows = parentForm.find(className),
            row = $(htmlFirstRow).insertAfter(rows.last()),
            inputs = row.find(":input"),
            remBtnPlace = row.find(".removeBtnPlace");

        remBtnPlace = remBtnPlace.length ? remBtnPlace : row;

        var remBtn = $('<input class="removeRow" type="button" value="X" />').appendTo(remBtnPlace);

        remBtn.on("click", removeRow.bind(remBtn, row, parentForm, className));

        for (var i = 0; i < inputs.length; i++) {
            var name = $(inputs[i]).attr("name");
            var newName = name.split("[")[0] + "[" + (rows.length + 1) + "][" + name.split("[").splice(2).join("[");

            $(inputs[i]).attr("name", newName);
            $(inputs[i]).val("");
        }

        parentForm.find(className).each(function (i) {
            if (i % 2 == 0) {
                $(this).addClass("cs_row_odd").removeClass("cs_row_even");
            } else {
                $(this).addClass("cs_row_even").removeClass("cs_row_odd");
            }
            var rowName = $(this).find(".rowName");
            if (rowName.length) {
                var h = rowName.html();
                h = h.split(" ");
                rowName.html( h[0] + " " + (i + 1) );
            }
        })
    }
    function removeRow(row, parent, className) {
        row.remove();
        var rows = parent.find(className);

        for (var k = 0; k < rows.length; k++) {
            var inputs = $(rows[k]).find(":input");
            
            for (var i = 0; i < inputs.length; i++) {
                var name = $(inputs[i]).attr("name");
                if (name) {
                    var newName = name.split("[")[0] + "[" + (k + 1) + "][" + name.split("[").splice(2).join("[");

                    $(inputs[i]).attr("name", newName);
                }
            }
        }
        rows.each(function (i) {
            if (i % 2 == 0) {
                $(this).addClass("cs_row_odd").removeClass("cs_row_even");
            } else {
                $(this).addClass("cs_row_even").removeClass("cs_row_odd");
            }
            var rowName = $(this).find(".rowName");
            if (rowName.length) {
                var h = rowName.html();
                h = h.split(" ");
                rowName.html(h[0] + " " + (i + 1));
            }
        })
    }
	
	/* Rule show and hide form options */
	 $("select[name*=AFFWP_GS_gs_rules]").each(showDivOnOption).change(showDivOnOption)

		function showDivOnOption() {
			if ($(this).attr("name").indexOf("[trigger]") > - 1) {
				var option = $(this).find(":selected").val();
				var parent = $(this).closest("tbody.gs_rules");
		
				if (option.length) {
					parent.find("div[id]").hide();
					parent.find("#" + option).show();
					}
				}
			}

	
});