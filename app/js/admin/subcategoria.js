$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    $(".table").stupidtable();
    //editar
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        var title = $(this).attr('name');
        var cat = $(this).attr('cat');
        $('#sub_categoria').val(cat);
        $('#add-categoria').find('b').html('Editar Subcategoria');
        $('#add-categoria').find('.icon-plus-sign').removeClass('icon-plus-sign').addClass('icon-edit');
        $('#collapseOne').collapse('show');
        $('#sub_title').val(title);
        $('#btn-add').html('Atualizar');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/incluir/','/atualizar/'+id+'/'));
        $('#sub_title').removeClass('invalid');
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#sub_categoria').val('');
        $('#collapseOne').collapse('hide'); 
        $('#add-categoria').find('b').html('Cadastrar Nova Subcategoria');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#sub_title').val('');
        $('#add-categoria').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#sub_title').removeClass('invalid');
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/subcategoria/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })      
    //event change
    $('#sub_categoria').live('change',function(){
        if($('#sub_categoria option:selected').val() != ""){
            $('#sub_categoria').removeClass('invalid');            
        }
    })
})
function valida(){
    if($('#sub_categoria option:selected').val() == ""){
        $('#sub_categoria').addClass('invalid');
        $('#sub_categoria').focus();
        return false;        
    }
    else{
        $('#sub_categoria').removeClass('invalid');
    }
    if($.trim($('#sub_title').val()) == ""){
        $('#sub_title').addClass('invalid');
        $('#sub_title').focus();
        return false;
    }
    else{
        $('#sub_title').removeClass('invalid');
    }
}

