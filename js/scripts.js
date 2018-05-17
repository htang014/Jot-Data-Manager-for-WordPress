jQuery(document).ready(function($){
    'use strict';

	$('.table-row-rearrange span').click(function() {
        var $this = $(this);
        var row = $this.parent().parent();

        var menuId = $this.parents('.wrap').attr('data-id');
        var upDown = $this.attr('data-up-down');
        var pos = row.attr('data-pos');

		$.post(plugin.url + "db-edit.php", 
		{
            'menu-id': menuId,
			'task': "row-reorder",
			'move': upDown,
			'position': pos,
		},
		function(data, status){
            console.log(data);
			location.reload();
		});
    });

    $('thead input:checkbox, tfoot input:checkbox').change(function() {
        var $this = $(this);
        if ($this.is(':checked')){
            $this.parents('table').find('input:checkbox').prop('checked', true);
        }
        else {
            $this.parents('table').find('input:checkbox').prop('checked', false);
        }
    });

    $('#remove-image-checkbox').change(function () {
        var $this = $(this);
        if ($this.is(':checked')){
            $('#upload-image-button').prop('disabled', true);
        }
        else {
            $('#upload-image-button').prop('disabled', false);
        }
    });

    $('#order-checkbox').change(function () {
        var $this = $(this);
        if ($this.is(':checked')){
            $('#order-by-select').prop('disabled', false);
        }
        else {
            $('#order-by-select').prop('disabled', true);
        }
    });

    $('#split-checkbox').change(function () {
        var $this = $(this);
        if ($this.is(':checked')){
            $('#split-by-select').prop('disabled', false);
        }
        else {
            $('#split-by-select').prop('disabled', true);
        }
    });

    $('#enable-picture-checkbox').change(function () {
        var $this = $(this);
        if ($this.is(':checked')){
            $('#picture-path-input').prop('disabled', false);
            $('#image-field-select').prop('disabled', false);
        }
        else {
            $('#picture-path-input').prop('disabled', true);
            $('#image-field-select').prop('disabled', true);
        }
    });

    $('#table-entry-select').change(function () {
        var $this = $(this);
        var menuName = $(this).parents('.wrap').attr('data-name');
        window.location.href = "admin.php?page=db-edit%2F"+menuName+"-edit.php&position=" + $this.attr("value");
    });

    $('#menu-select').change(function () {
        $(this).parents('form').submit();
    });

    $('#table-select').change(function () {
        $(this).parents('form').submit();
    });

    $('.generic-form').submit(function(e) {
        $(this).find('.form-required').each(function() {
            if (!$(this).find(".form-input").val()){
                e.preventDefault(); 
                $(this).addClass("form-invalid");
            }
        });
    });

    $('.ajax-form').submit(function(e) {
        e.preventDefault();    
        var formData = new FormData(this);
        var menuName = $(this).parents('.wrap').attr('data-name');
        var action = $(this).attr("action");
        var err = false;

        $(this).find('.form-required').each(function() {
            if (!$(this).find(".form-input").val()){
                err = true;
                $(this).addClass("form-invalid");
            }
        });

        // for (var pair of formData.entries()) {
        //     console.log(pair[0]+ ', ' + pair[1]); 
        // }

        if (err) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: action,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(response){
                console.log(response);
                //console.log("admin.php?page=db-edit%2F"+menuName+"-list.php");
                if (menuName){
                    window.location.href = "admin.php?page=db-edit%2F"+menuName.replace(/\s/g,'+')+"-list.php";
                }
                else {
                    location.reload();
                }
            },
            error: function(response){
                console.log(response);
            }
        });
        return false;
    }); 
}); 