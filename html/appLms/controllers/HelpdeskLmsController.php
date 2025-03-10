<?php

use FormaLms\lib\Domain\DomainHandler;

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

ini_set('display_errors', 1);

class HelpdeskLmsController extends LmsController
{
    public function show()
    {
        $sender = DomainHandler::getInstance()->getMailerField('sender_mail_system');
        $sender_name = DomainHandler::getInstance()->getMailerField('helper_desk_name') ?? DomainHandler::getInstance()->getMailerField('sender_name_system');
        $prefix_subj = DomainHandler::getInstance()->getMailerField('helper_desk_subject');
        $ccn = FormaLms\lib\Get::sett('send_ccn_for_system_emails');
        $sendto = $_POST['sendto'];
        $usermail = $_POST['email'];
        $content = nl2br($_POST['msg']);
        $telefono = $_POST['telefono'];
        $username = $_POST['username'];
        $oggetto = $_POST['oggetto'];
        $copia = $_POST['send_cc'];
        $priorita = $_POST['priorita'];

        $help_req_resolution = $_POST['help_req_resolution'];

        $subject = $prefix_subj ? '[' . $prefix_subj . '] ' . $oggetto : $oggetto;

        $headers = "From: \"$sender_name\" <" . strip_tags($sendto) . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html;charset=utf-8 \r\n";
        if ($copia == 'on') {
            $headers .= 'Cc: ' . $usermail . "\r\n";
        }
        $headers .= 'Ccn: ' . $ccn . "\r\n";
        if ($priorita != 'on') {
            //SET EMAIL PRIORITY
            $headers .= "X-Priority: 1 (Higuest)\n";
            $headers .= "X-MSMail-Priority: High\n";
            $headers .= "Importance: High\n";
        }

        $br_char = '<br>';

        $msg = "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>" . $sender_name . "</h2>\r\n";
        $msg .= '<p><strong>' . Lang::t('_USER', 'standard') . ':</strong> ' . $username . "</p>\r\n";
        $msg .= '<p><strong>' . Lang::t('_EMAIL', 'menu') . ':</strong> ' . $usermail . "</p>\r\n";
        if ($telefono) {
            $msg .= '<p><strong>' . Lang::t('_PHONE', 'classroom') . ':</strong> ' . $telefono . "</p>\r\n";
        }
        $msg .= '<p><strong>' . Lang::t('_TEXTOF', 'menu') . ':</strong> ' . $content . "</p>\r\n";

        $id_course = $this->session->get('idCourse');
        if ($id_course) {
            $sql = "SELECT c.code, c.name FROM %lms_course AS c WHERE c.idCourse = $id_course";
            $query = sql_query($sql);

            if ($row = sql_fetch_object($query)) {
                $msg .= '<p><strong>' . Lang::t('_COURSE', 'standard') . ':</strong> [' . $row->code . '] - ' . $row->name . "</p>\r\n";
            }
        }

        $msg .= $br_char . '---------- CLIENT INFO -----------' . $br_char;
        $msg .= 'USER AGENT: ' . $_SERVER['HTTP_USER_AGENT'] . $br_char;
        $msg .= 'RESOLUTION: ' . $help_req_resolution . $br_char;


        $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();
        $mailer->addReplyTo(strip_tags($usermail));

        if ($mailer->SendMail([$sendto], $subject, $msg, $sender, [], [MAIL_HEADERS => $headers])) {
            echo 'true';
        } else {
            echo Lang::t('_NO_EMAIL_CONFIG', 'standard');
        }
        exit();
    }
}
