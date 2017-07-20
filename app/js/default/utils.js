
//change button do ifrMessage $("body",top.document).contents().find(".my-custom-button-class > .ui-button-text").text("Enviado");
//submit buttton ifrMessage $("#ifr").contents().find('form').submit();

function message(t,msg,w,h)
{
    $('body').append('<div id="dialog" title="'+t+'">'+msg+'</div>')
    $( "#dialog" ).dialog({
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},	
	modal: true,
	width: w,
	height: h,
	buttons: {
	    "Ok": function() {
		$(this).dialog("close")
		$("#dialog").dialog("close")
		$("#dialog").remove()
	    }
	}
    })
}

function ifrFormSubmit()
{
    $( "#ifr").contents().find("form").submit()
}

function IfrEdit(t,url,w,h,refresh)
{
    var htmlpage = '<iframe src="'+url+'" scrolling="no" width="'+(w-25)+'" name="ifr"  id="ifr"'
    htmlpage += 'height="'+(h-100)+'" style="padding:0px; border:0px solid red"></iframe>'
    $('body').append('<div id="dialog"  class="dialogr pad-20" title="'+t+'">'+htmlpage+'</div>')
    
    $( "#dialog" ).dialog({
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},	
	close: function(event,ui){
	    if(refresh != false)
	    {
		window.location = window.location
	    }
	},
	modal: true,
	width: w,
	height: h,
	buttons: {
	    "Gravar": function() {
		ifrFormSubmit()
	    },    
	    "Cancelar": function() {
		refresh = false
		$(this).dialog("close")
		$("#dialog").remove()
	    }	    
	}
    })    
}


function messageIframe(t,url,w,h,refresh)
{
    var htmlpage = '<iframe src="'+url+'" scrolling="no" width="'+(w-30)+'" name="ifr"  id="ifr"'
    htmlpage += 'height="'+(h-120)+'" style="padding:0px; border:1px solid #fff"></iframe>'
    
    $('body').append('<div id="dialog"  class="dialogr pad-20" title="'+t+'">'+htmlpage+'</div>')
    $( "#dialog" ).dialog({
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},
	close: function(event,ui){
	    if(refresh != false)
	    {
		window.location = window.location
	    }
	},    
	modal: true,
	width: w,
	height: h,
	buttons: {
	    "Fechar": function() {
		$(this).dialog("close")
		$("#dialog").remove()
	    }
	}
    })    
}


function messageOk()
{
    $('body').append('<div id="dialog" title="Status:"><br />Procedimento realizado com sucesso!</div>')
    $( "#dialog" ).dialog({
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},	
	modal: true,
	width: 300,
	height: 120,
	buttons: {
	    "Ok": function() {
		$(this).dialog("close")
		$('#dialog').dialog("close")
		$("#dialog").remove()
	    }
	}
    })
}

function messageOkAndClose()
{
    $('body').append('<div id="dialog" title="Status:"><br />Procedimento realizado com sucesso!</div>')
    $( "#dialog" ).dialog({
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},	
	modal: true,
	width: 300,
	height: 140,
	buttons: {
	    "Ok": function() {
		window.parent.$('#dialog').dialog('close')
		window.parent.$('#dialog').remove()
		$(this).dialog("close")
	    }
	}
    })
}

function messageError(msg)
{
    if(msg == '')
    {
	var msg = 'Houve um erro ao realizar o procedimento'
    }
    
    $('body').append('<div id="dialog" title="Mensagem"><br /><p>'+msg+'</p></div>')
    $( "#dialog" ).dialog({
	modal: true,
	width: 330,
	height: 150,
	buttons: {
	    "Ok": function() {
		$(this).dialog("close")
		$('#dialog').dialog("close")
		$("#dialog").remove()
	    }
	}
    })
}


function messageAlert(msg)
{
    if(msg == '')
    {
	var msg = ''
    }
    $('body').append('<div id="dialog" title="Mensagem"><br /><p>'+msg+'</p></div>')
    $( "#dialog" ).dialog({
	//modal: true,
	open: function(event, ui) { 
	    $(this).parent().children().children('.ui-dialog-titlebar-close').hide()
	},	
	width: 330,
	height: 120,
	buttons: {
	    "Ok": function() {
		$(this).dialog("close")
		$('#dialog').dialog("close")
		$("#dialog").remove()
	    }
	}
    })
}


function checkMail(email){
    var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
    var mail = email
    var flag = 1;
    if(typeof(mail) == "string"){
	if(er.test(mail)){
	    flag = 0;
	}
    }else if(typeof(mail) == "object"){
	if(er.test(mail.value)){
	    flag = 0;
	}
    }else{
	flag = 0;
    }
    if(flag == 1)
    {
	return false
    }
    else
    {
	return true
    }
}

function validate(elm)
{
    if($.trim($('#'+elm).val()) == '')
    {
	$('#'+elm).addClass('invalid')
	$('#'+elm).focus()
	return false
    }
    else
    {
	$('#'+elm).removeClass('invalid')
	return true
    }
}


function myCloseAllDialogs()
{
    $(".ui-dialog").dialog("close")
    $(".ui-dialog").remove()    
}


function getJson(url)
{
    var json;   
    json = eval($.ajax({
	url:url,
	async: false
    }).responseText)
    
    return json;
}