<style>
    .activo {
        background-color: #d4edda !important; /* Verde claro */
        font-weight: bold;
    }

    .no-activo{
        background-color: #f8d7da !important; /* Rojo claro */
        font-weight: bold;
    }
</style>
<?php 

function deae_customers_page() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";

    // Obtener filtros
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Construir consulta con filtro
    $query = "SELECT * FROM $table_customers WHERE 1=1";
    if ($search) {
        $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR document_id LIKE '%$search%')";
    }

    $customers = $wpdb->get_results($query);

    echo '<div class="wrap">';
    echo '<h1>Clientes Registrados</h1>';

    // Formulario de búsqueda
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="deae_customers">';
    echo '<input type="text" name="search" placeholder="Buscar por nombre, email o documento" value="' . esc_attr($search) . '">';
    echo '<button type="submit" class="button">🔍 Buscar</button>';
    echo '</form>';

    // Botón de exportación CSV
    echo '<a href="' . admin_url('admin-post.php?action=export_deae_customers') . '" class="button button-primary">📤 Exportar CSV</a>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Cédula</th>
                <th>Id de Registro</th>
                <th>Tipo de Suscripción</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Fecha de Suscripción</th>
                <th>Última Pago</th>
                <th>Acciones</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($customers as $customer) {
        $esActivo = $customer->estado_suscripcion ? true : false;
        $estado = $esActivo ? "✅ Activa" : "❌ Inactiva";
        $claseFila = $esActivo ? "class='activo'" : "class='no-activo'";

        echo "<tr $claseFila>
                <td>{$customer->id}</td>
                <td>{$customer->name}</td>
                <td>{$customer->email}</td>
                <td>{$customer->phone}</td>
                <td>{$customer->document_id}</td>
                <td>{$customer->registration_id}</td>
                <td>{$customer->tipo_suscripcion}</td>
                <td>\${$customer->monto_suscripcion}</td>
                <td>{$estado}</td>
                <td>{$customer->created_at}</td>
                <td>{$customer->ultimo_pago_suscripcion}</td>
                <td>
                    <a href='" . admin_url("admin-post.php?action=process_subscription_payment&id={$customer->id}") . "' class='button button-primary'>💳 Pagar</a>
                    <a href='" . admin_url("admin.php?page=deae_customers_edit&id={$customer->id}") . "' class='button'>✏️ Editar</a>
                    <a href='" . admin_url("admin-post.php?action=delete_deae_customer&id={$customer->id}") . "' class='button button-danger' onclick='return confirm(\"¿Eliminar este cliente?\");'>🗑️ Eliminar</a>
                    <a href='" . admin_url("admin-post.php?action=stop_subscription&id={$customer->id}") . "' class='button button-secondary'>Desuscribir</a>
                </td>
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}