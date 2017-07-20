<!DOCTYPE html>
<html>
    <head>
        <title>${msg:presurvey.title}</title>
        <link rel="shortcut icon" href="${webimroot}/images/favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" type="text/css" href="${tplroot}/bootstrap/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="${tplroot}/bootstrap/css/bootstrap-responsive.css" />
        <script src="${tplroot}/jquery-1.8.3.min.js" type="text/javascript"></script>
        <script src="${tplroot}/bootstrap/js/bootstrap.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="${tplroot}/chat.css" />
    </head>
    <body style="background-color: #f8f8f8; ">
        <div id="main" class="span7" style="background-color: #f8f8f8; padding-left:40px">

            <br /> <img src="${tplroot}/atd_on.gif" /> <br /><br />

            <form name="surveyForm" method="post" action="${webimroot}/client.php" class="form">

                <input type="hidden" name="style" value="${styleid}"/>
                <input type="hidden" name="info" value="${form:info}"/>
                <input type="hidden" name="referrer" value="${page:referrer}"/>
                <input type="hidden" name="survey" value="on"/>

                ${ifnot:showemail}<input type="hidden" name="email" value="${form:email}"/>${endif:showemail}
                ${ifnot:groups}${if:formgroupid}<input type="hidden" name="group" value="${form:groupid}"/>${endif:formgroupid}${endif:groups}
                ${ifnot:showmessage}<input type="hidden" name="message" value="${form:message}"/>${endif:showmessage}


                <table id="form" class="table">
                    ${if:errors}
                    <tr>
                        <td colspan="2">
                            <table cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="top"><img id="errorimage" src="${tplroot}/images/error.gif" border="0" alt=""/></td>
                                    <td>${errors}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    ${endif:errors}

                    ${if:groups}
                    <tr>
                        <td class="text">Corretor:</td>
                        <td>
                            <select name="group" id="oper" style="min-width:200px;">${page:groups}</select>
                        </td>
                    </tr>
                    ${endif:groups}

                    <tr>
                        <td class="text">Seu Nome:</td>							
                        <td>
                            <input type="text" name="name" value="${form:name}" class="field" ${ifnot:showname}disabled="disabled"${endif:showname}/>
                        </td>
                    </tr>

                    ${if:showemail}
                    <tr>
                        <td class="text">Seu E-mail:</td>
                        <td>
                            <input type="text" name="email" value="${form:email}" class="field"/>
                        </td>
                    </tr>
                    ${endif:showemail}
                    ${if:showmessage}
                    <tr>
                        <td class="text">Pergunta:</td>
                        <td>
                            <input type="text" name="message" value="${form:message}" class="field"/>
                        </td>
                    </tr>
                    ${endif:showmessage}

                </table>

            </form>
            <p>
                <a href="javascript:document.surveyForm.submit();" title="iniciar chat" class="btn btn-small btn-success">Entrar</a>
                <a href="javascript:;" title="encerar chat" class="btn btn-small btn-danger">Sair</a>
            </p>
        </div>
        <script type="text/javascript">
            $(function(){
                $('.btn-danger').live('click',function(){
                    parent.jQuery.fancybox.close()
                });
            })
        </script>
    </body>
</html>
