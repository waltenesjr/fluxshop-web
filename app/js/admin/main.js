$(function(){
    
    //notify plugin
    $('head').append('<link href="js/jquery/notify/style.css" rel="stylesheet" type="text/css" />');
    $('head').append('<script src="js/jquery/notify/notify.js" type="text/javascript"></script>');
    //cancel
    $('.cancel').live('click',function(){
        $('#collapseOne').collapse('hide'); 
    })
    //tables
    $('.tabler b').tooltip({
        placement:'top'
    });
    $('.btn-action').live('click',function(){
        //$(this).button('loading');
        });
    $('.tips').tooltip({
        placement:'right'
    });  
    $('.tips-top').tooltip({
        placement:'top'
    });    
    $('.tips-left').tooltip({
        placement:'left'
    });    
    $('.tips-bottom').tooltip({
        placement:'bottom'
    });    
    $('.tips-right').tooltip({
        placement:'right'
    });   
//$('#dash').popover({placement:'right',title:'Dashboard',html: true, content:'Informações das últimas ocorrências do site'});
})

function popr(elm,title,msg,place) {
    $('#'+elm).popover({
        placement:place,
        title:title,
        html: true, 
        content: msg
    }); 
    $('#'+elm).popover('show');
}


function refreshTips() {
       
    $('.tips').tooltip('hide');
    $('.tips').removeData('tooltip'); 
    $('.tips').tooltip({
        placement:'right'
    });  
    $('.tips-top').tooltip('hide');
    $('.tips-top').removeData('tooltip'); 
    $('.tips-top').tooltip({
        placement:'top'
    });    
    $('.tips-left').tooltip('hide');
    $('.tips-left').removeData('tooltip'); 
    $('.tips-left').tooltip({
        placement:'left'
    });    
    $('.tips-bottom').tooltip('hide');
    $('.tips-bottom').removeData('tooltip'); 
    $('.tips-bottom').tooltip({
        placement:'bottom'
    });    
    $('.tips-right').tooltip('hide');
    $('.tips-right').removeData('tooltip'); 
    $('.tips-right').tooltip({
        placement:'right'
    });    
}
