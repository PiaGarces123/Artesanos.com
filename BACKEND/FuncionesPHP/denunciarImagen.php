<?php
    // Requerimos todas las clases necesarias
    require_once "../Clases/Complaint.php";
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    session_start();
    header('Content-Type: application/json');

    // 1. Validar Sesión
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
        exit;
    }

    $conn = conexion();
    $myId = (int)$_SESSION['user_id'];

    // 2. Obtener Datos
    $imageId = (int)($_POST['imageId'] ?? 0);
    $reason = trim($_POST['reportReason'] ?? "");

    if (empty($imageId) || empty($reason) || strlen($reason) > 255) {
        echo json_encode(["status" => "error", "message" => "Motivo inválido o vacío (máx 255 caracteres)."]);
        desconexion($conn);
        exit;
    }

    try {
        // =====================================================
        // 1. VERIFICAR SI EL USUARIO PUEDE DENUNCIAR
        // =====================================================
        if (!Complaint::puedeDenunciar($conn, $imageId, $myId)) {
            echo json_encode([
                "status" => "error", 
                "message" => "Ya tienes una denuncia pendiente para esta imagen. Espera a que sea resuelta."
            ]);
            desconexion($conn);
            exit;
        }

        // =====================================================
        // 2. AGREGAR LA DENUNCIA (Y OBTENER SU ID)
        // =====================================================
        // (Asumiendo que modificaste Complaint::agregar para que devuelva el ID)
        $newComplaintId = Complaint::agregar($conn, $imageId, $myId, $reason);

        if ($newComplaintId) {
            
            $success = true; // Bandera para rastrear el éxito
            
            // =====================================================
            // 3. ACTUALIZAR ESTADO DE REVISIÓN DE LA IMAGEN
            // =====================================================
            // (Asumiendo que modificaste Imagen::actualizarRevision para que devuelva true/false)
            if (!Imagen::actualizarRevision($conn, $imageId)) {
                $success = false; // Falló el paso 3
            }

            // =====================================================
            // 4. ACTUALIZAR ESTADO DE PUBLICACIÓN DEL DUEÑO
            // =====================================================
            
            if ($success) { // Solo continuar si el paso 3 fue bien
                $imageData = Imagen::getById($conn, $imageId);
                
                if ($imageData && isset($imageData['I_idUser'])) {
                    $imageOwnerId = (int)$imageData['I_idUser'];
                    
                    // (Asumiendo que modificaste User::actualizarEstadoPublicacion para que devuelva true/false)
                    if (!User::actualizarEstadoPublicacion($conn, $imageOwnerId)) {
                        $success = false; // Falló el paso 4
                    }
                } else {
                    $success = false; // Falló al obtener los datos de la imagen
                }
            }
            
            // =====================================================
            // 5. RESPUESTA FINAL (ÉXITO O ROLLBACK MANUAL)
            // =====================================================
            
            if ($success) {
                // ¡Todo salió bien!
                echo json_encode([
                    "status" => "success",
                    "message" => "Denuncia enviada. Gracias por tu reporte."
                ]);
            } else {
                // ¡Algo falló! Deshacemos la denuncia original
                // (Tu clase Complaint ya tiene el método 'eliminar')
                Complaint::eliminar($conn, $newComplaintId); 
                
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al procesar las reglas de la denuncia. La denuncia ha sido revertida."
                ]);
            }

        } else {
            // Falló el Complaint::agregar
            throw new Exception("No se pudo registrar la denuncia en la base de datos.");
        }

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

    desconexion($conn);
?>