<?php 
function deae_transactions_page() {
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";

    // Obtener filtros
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Construir consulta con filtro
    $query = "SELECT * FROM $table_transactions WHERE 1=1";
    if ($search) {
        $query .= " AND (customer_name LIKE '%$search%' OR customer_email LIKE '%$search%')";
    }

    $transactions = $wpdb->get_results($query);

    echo '<div class="wrap">';
    echo '<h1>Transacciones Registradas</h1>';

    // Formulario de búsqueda
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="deae_transactions">';
    echo '<input type="text" name="search" placeholder="Buscar por cliente o email" value="' . esc_attr($search) . '">';
    echo '<button type="submit" class="button">🔍 Buscar</button>';
    echo '</form>';

    // Botón de exportación CSV
    echo '<a href="' . admin_url('admin-post.php?action=export_deae_transactions') . '" class="button button-primary">📤 Exportar CSV</a>';

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Transacción</th><th>RegistrationId</th><th>Monto</th><th>Cliente</th><th>Email</th><th>Tarjeta</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';

    foreach ($transactions as $transaction) {
        echo "<tr>
                <td>{$transaction->id}</td>
                <td>{$transaction->transaction_id}</td>
                <td>{$transaction->registration_id}</td>
                <td>\${$transaction->amount}</td>
                <td>{$transaction->customer_name}</td>
                <td>{$transaction->customer_email}</td>
                <td>{$transaction->card_last4}</td>
                <td>✅ Pago Exitoso</td>
                <td>{$transaction->created_at}</td>
                <td>
                    <a href='" . admin_url("admin-post.php?action=delete_deae_transaction&id={$transaction->id}") . "' class='button button-danger' onclick='return confirm(\"¿Eliminar esta transacción?\");'>🗑️ Eliminar</a>
                </td>
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}