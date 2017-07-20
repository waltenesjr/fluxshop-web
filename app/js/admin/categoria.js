$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    $(".table").stupidtable();
    //editar
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        var title = $(this).attr('name');
        $('#add-categoria').find('b').html('Editar Categoria');
        $('#add-categoria').find('.icon-plus-sign').removeClass('icon-plus-sign').addClass('icon-edit');
        $('#collapseOne').collapse('show');
        $('#categoria_title').val(title);
        $('#btn-add').html('Atualizar');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/incluir/','/atualizar/'+id+'/'));
        $('#categoria_title').removeClass('invalid');
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#collapseOne').collapse('hide'); 
        $('#add-categoria').find('b').html('Cadastrar Nova Categoria');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#categoria_title').val('');
        $('#add-categoria').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#categoria_title').removeClass('invalid');
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/categoria/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })        
})
function valida()
{
    if($.trim($('#categoria_title').val()) == "")
    {
        $('#categoria_title').addClass('invalid');
        $('#categoria_title').focus();
        //$('#categoria_title').popover({placement:'top',title:'Campo Requerido',html: true, content:'Você precisa selecionar uma Categoria!'});
        return false;
    }
    else
    {
        $('#categoria_title').removeClass('invalid');
        return true;
    }
}

