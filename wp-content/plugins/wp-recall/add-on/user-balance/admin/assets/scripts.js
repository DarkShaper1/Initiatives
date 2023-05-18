jQuery(function () {
    /*************************************************
     Пополняем личный счет пользователя в админке
     *************************************************/
    jQuery('.wp-list-table .edit_balance').on('click', function () {

        let id_user = parseInt(jQuery(this).attr('id').replace(/\D+/g, ''));

        rcl_ajax({
            data: {
                action: 'rcl_edit_balance_user',
                user: id_user,
                balance: jQuery('.balanceuser-' + id_user).val()
            }
        });

        return false;

    });
});