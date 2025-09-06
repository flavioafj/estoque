<?php
namespace Models;

use PDOException;
use Helpers\Session;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; 

class Alert extends BaseModel {
    protected $table = 'alertas_estoque';

    public function checkLowStock($produto_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vw_produtos_estoque_critico WHERE id = :produto_id");
            $stmt->bind(':produto_id', $produto_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result) {
                // Checa se já existe alerta pendente para evitar duplicatas
                $checkStmt = $this->db->prepare("SELECT id FROM alertas_estoque WHERE produto_id = :produto_id AND tipo_alerta = 'MINIMO' AND lido = FALSE");
                $checkStmt->bind(':produto_id', $produto_id, \PDO::PARAM_INT);
                $checkStmt->execute();
                if ($checkStmt->fetch()) {
                    return; // Alerta pendente já existe
                }

                $mensagem = "Produto {$result['nome']} com estoque baixo: {$result['estoque_atual']} <= {$result['estoque_minimo']}.";
                $insertStmt = $this->db->prepare("INSERT INTO alertas_estoque (produto_id, tipo_alerta, mensagem, lido, criado_em) VALUES (:produto_id, 'MINIMO', :mensagem, FALSE, CURRENT_TIMESTAMP)");
                $insertStmt->bind(':produto_id', $produto_id, \PDO::PARAM_INT);
                $insertStmt->bind(':mensagem', $mensagem, \PDO::PARAM_STR);
                $insertStmt->execute();
                $alert_id = $this->db->lastInsertId();

                // Registra na auditoria
                $this->logAudit('alertas_estoque', $alert_id, 'INSERT', null, ['produto_id' => $produto_id, 'mensagem' => $mensagem]);

                // Envia alerta
                $this->sendAlert($alert_id);
            }
        } catch (PDOException $e) {
            error_log("Alert::checkLowStock: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
        }
    }

    public function sendAlert($alert_id) {
        try {
            $stmt = $this->db->prepare("SELECT a.*, p.nome, p.estoque_atual, p.estoque_minimo FROM alertas_estoque a JOIN produtos p ON a.produto_id = p.id WHERE a.id = :alert_id");
            $stmt->bind(':alert_id', $alert_id, \PDO::PARAM_INT);
            $stmt->execute();
            $alert = $stmt->fetch();

            if ($alert) {
                // Envio de e-mail
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $_ENV['SMTP_HOST'] ?: 'smtp.exemplo.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USER'] ?: 'seu_email@exemplo.com';
                $mail->Password = $_ENV['SMTP_PASS'] ?: 'sua_senha';
                $mail->SMTPSecure = 'tls';
                $mail->Port = $_ENV['SMTP_PORT'] ?: 587;

                $mail->setFrom('no-reply@sorveteria.com', 'Sistema de Estoque');
                $admins = (new User())->getAdmins();
                foreach ($admins as $admin) {
                    $mail->addAddress($admin['email']);
                }
                $mail->isHTML(true);
                $mail->Subject = 'Alerta de Estoque Baixo';
                $mail->Body = "<p>{$alert['mensagem']}</p><p>Produto: {$alert['nome']}</p><p>Estoque Atual: {$alert['estoque_atual']}</p><p>Estoque Mínimo: {$alert['estoque_minimo']}</p>";
                // Antes de $mail->send();
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug [$level]: $str </br>", 3, __DIR__ . '/../../logs/error.log');
                };
                $mail->send();

                // Local para futura implementação de WhatsApp
                // $this->sendWhatsApp($alert);
            }
        } catch (Exception $e) {
            error_log("Alert::sendAlert: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
        }
    }

    public function markAsRead($alert_id, $usuario_id) {
        try {
            $stmt = $this->db->prepare("UPDATE alertas_estoque SET lido = TRUE, usuario_leitura_id = :usuario_id, data_leitura = CURRENT_TIMESTAMP WHERE id = :alert_id");
            $stmt->bind(':alert_id', $alert_id, \PDO::PARAM_INT);
            $stmt->bind(':usuario_id', $usuario_id, \PDO::PARAM_INT);
            $stmt->execute();

            // Registra na auditoria
            $this->logAudit('alertas_estoque', $alert_id, 'UPDATE', ['lido' => false], ['lido' => true]);
        } catch (PDOException $e) {
            error_log("Alert::markAsRead: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
        }
    }

    public function getPendingAlerts() {
        try {
            $stmt = $this->db->prepare("SELECT a.id, a.produto_id, a.tipo_alerta, a.mensagem, a.criado_em, p.nome FROM alertas_estoque a JOIN produtos p ON a.produto_id = p.id WHERE a.lido = FALSE ORDER BY a.criado_em DESC LIMIT 10");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Alert::getPendingAlerts: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            return [];
        }
    }

    // Placeholder para futura implementação de WhatsApp
    private function sendWhatsApp($alert) {
        // Futura implementação usando API externa (ex.: Twilio)
        // Exemplo: $twilio = new Client(getenv('TWILIO_SID'), getenv('TWILIO_TOKEN'));
        // $twilio->messages->create('whatsapp:+numero', ['from' => 'whatsapp:+seu_numero', 'body' => $alert['mensagem']]);
    }
}