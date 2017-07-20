$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    $(".table").stupidtable();
    //editar
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        var title = $(this).attr('name');
        $('#add-area').find('b').html('Editar Área');
        $('#add-area').find('.icon-plus-sign').removeClass('icon-plus-sign').addClass('icon-edit');
        $('#collapseOne').collapse('show');
        $('#area_title').val(title);
        $('#btn-add').html('Atualizar');
        $('#f-area').attr('action',$('#f-area').attr('action').replace('/incluir/','/atualizar/'+id+'/'));
        $('#area_title').removeClass('invalid');
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#collapseOne').collapse('hide'); 
        $('#add-area').find('b').html('Cadastrar Nova Área');
        $('#f-area').attr('action',$('#f-area').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#area_title').val('');
        $('#add-area').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#area_title').removeClass('invalid');
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/area/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })        
})
function valida()
{
    if($.trim($('#area_title').val()) == "")
    {
        $('#area_title').addClass('invalid');
        $('#area_title').focus();
        //$('#area_title').popover({placement:'top',title:'Campo Requerido',html: true, content:'Você precisa selecionar uma Área!'});
        return false;
    }
    else
    {
        $('#area_title').removeClass('invalid');
        return true;
    }
}

