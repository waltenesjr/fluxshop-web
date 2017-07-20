<?php

class Sendmail extends PHPFrodo
{

    public function __construct()
    {
        parent:: __construct();
    }

    public function sender( $n = array( ) )
    {
        $this->select()->from( 'smtp' )->execute();
        if ( $this->result() )
        {
            $m = ( object ) $this->data[0];
            $this->helper( 'mail' );
            global $mail;
            $mail->Port = $m->smtp_port;
            $mail->Host = "$m->smtp_host";
            $mail->Username = $m->smtp_username;
            $mail->Password = $m->smtp_password;
            $mail->From = $m->smtp_username;
            $mail->FromName = $m->smtp_fromname;
            $mail->Subject = $n['subject'];
            $mail->Body = $n['body'];
            $mail->ClearAllRecipients();
            if ( $m->smtp_bcc != "" )
            {
                $mail->AddBCC( $m->smtp_bcc );
            }
            if ( isset( $n['email'] ) )
            {
                $mail->AddAddress( $n['email'] );
            }
            else
            {
                $mail->AddAddress( $m->smtp_username );
            }
            //$mail->AddReplyTo( $m->smtp_replyto );
            if ( @$mail->Send() )
            {
                return true;
            }
            else
            {
                return false;
                //echo "Erro: $mail->ErrorInfo <br/> Provaveis causas: <br> - E-mail, Senha, Porta ou Servidor SMTP incorretos.";
            }
            $mail->ClearAllRecipients();
        }
    }
}
