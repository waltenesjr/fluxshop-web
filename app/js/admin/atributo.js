$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    $(".table").stupidtable();
    //editar
    $('.attit').live('click',function(){
        var id = $(this).attr('id');
        window.location = baseUri + '/admin/atributo/editar/' + id + '/';
    })
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        var title = $(this).attr('name');
        $('#add-categoria').find('b').html('Editar Atributo');
        $('#add-categoria').find('.icon-plus-sign').removeClass('icon-plus-sign').addClass('icon-edit');
        $('#collapseOne').collapse('show');
        $('#atributo_nome').val(title);
        $('#btn-add').html('Atualizar');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/incluir/','/atualizar/'+id+'/'));
        $('#atributo_nome').removeClass('invalid');
        $('#atributo_nome').focus();
    })
    $('.editattr').live('click',function(){
        var id = $(this).attr('id');
        var at = $(this).attr('at');
        var title = $(this).attr('name');
        $('#add-categoria').find('b').html('Editar Item');
        $('#add-categoria').find('.icon-plus-sign').removeClass('icon-plus-sign').addClass('icon-edit');
        $('#collapseOne').collapse('show');
        $('#iattr_nome').val(title);
        $('#btn-add').html('Atualizar');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/additem/','/atualizaitem/'+id+'/'));
        $('#iattr_nome').removeClass('invalid');
        $('#iattr_nome').focus();
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#collapseOne').collapse('hide'); 
        $('#add-categoria').find('b').html('Cadastrar Novo Atributo');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#atributo_nome').val('');
        $('#add-categoria').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#atributo_nome').removeClass('invalid');
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/atributo/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })        
    //remove
    $('.removeattr').live('click',function(){
        var id = $(this).attr('id');
        var at = $(this).attr('at');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/atributo/removeritem/'+id+'/'+at+'/';
        $('#btn-remove').attr('href',url);
    })        
})
function valida()
{
    if($.trim($('#atributo_nome').val()) == "")
    {
        $('#atributo_nome').addClass('invalid');
        $('#atributo_nome').focus();
        //$('#atributo_nome').popover({placement:'top',title:'Campo Requerido',html: true, content:'Você precisa selecionar uma Atributo!'});
        return false;
    }
    else
    {
        $('#atributo_nome').removeClass('invalid');
        return true;
    }
}
function validaItem()
{
    if($.trim($('#iattr_nome').val()) == "")
    {
        $('#iattr_nome').addClass('invalid');
        $('#iattr_nome').focus();
        //$('#atributo_nome').popover({placement:'top',title:'Campo Requerido',html: true, content:'Você precisa selecionar uma Atributo!'});
        return false;
    }
    else
    {
        $('#iattr_nome').removeClass('invalid');
        return true;
    }
}

