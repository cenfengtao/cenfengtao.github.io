/**
 * 目录操作
 * */
var menu = {
    getMenu: function (id) {
        if (!id) {
            dialog.error('id不能为空');
        }
        var url = SCOPE.get_url;
        $.post(url, {'id': id}, function (result) {
            if (result.status == 1) {
                return layer.open({
                    type: 1,
                    title: '修改菜单',
                    skin: 'layui-layer-rim',
                    area: ['420px', '280px'], //宽高
                    content: "<form style='margin-top:20px;' id='demo-form2' data-parsley-validate class='form-horizontal form-label-left form-youpei'><div class='form-group'>" +
                    "<label class='control-label col-md-3 col-sm-3 col-xs-12'>菜单名称 <span class='required'>*</span></label>" +
                    "<div class='col-md-6 col-sm-6 col-xs-12'>" +
                    "<input type='text' name='title' required='required' value='" + result.data['title'] + "' class='form-control col-md-7 col-xs-12'>" +
                    "</div></div>" +
                    "<div class='form-group'>" +
                    "<label class='control-label col-md-3 col-sm-3 col-xs-12'>地址 </label>" +
                    "<div class='col-md-6 col-sm-6 col-xs-12'>" +
                    "<input type='text' name='url' value='" + result.data['url'] + "' class='form-control col-md-7 col-xs-12'>" +
                    "</div></div>" +
                    "<div class='form-group'>" +
                    "<label class='control-label col-md-3 col-sm-3 col-xs-12'>排序 </label>" +
                    "<div class='col-md-6 col-sm-6 col-xs-12'>" +
                    "<input type='text' name='sort' value='" + result.data['sort'] + "' class='form-control col-md-7 col-xs-12'>" +
                    "<input type='hidden' name='id' value='" + result.data['id'] + "'>" +
                    "</div></div>" +
                    "<div class='form-group'>" +
                    "<div class='col-md-6 col-sm-6 col-xs-12 col-md-offset-3'>" +
                    "<button class='btn btn-primary' type='reset'>重置</button>" +
                    "<button type='button'  class='btn btn-success' onclick='menu.edit()'>提交</button>" +
                    "</div></div></form>"
                });
            } else {
                return dialog.error(result.message);
            }
        }, 'JSON');
    },
    edit: function () {
        var title = $('input[name="title"]').val();
        var url = $('input[name="url"]').val();
        var sort = $('input[name="sort"]').val();
        var id = $('input[name="id"]').val();
        var data = {'id': id, 'title': title, 'url': url, 'sort': sort};
        if (!title) {
            return dialog.error('菜单名称不能为空');
        }
        if (!id) {
            return dialog.error('id错误');
        }
        var edit_url = SCOPE.edit_url;
        var success_url = SCOPE.success_url;
        $.post(edit_url, data, function (result) {
            if (result.status == 1) {
                return dialog.success(result.message, success_url);
            } else {
                return dialog.error(result.message);
            }
        }, 'JSON');
    }
};

$('.panel-heading').each(function () {
    $(this).mouseover(function () {
        $(this).find('.father_edit').show();
    });
    $(this).mouseout(function () {
        $(this).find('.father_edit').hide();
    });
});