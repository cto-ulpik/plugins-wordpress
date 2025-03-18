<?php

function deae_dashboard_page() {
    echo '<div class="wrap">';
    echo '<h1>Panel de Administración - Pagos DEAE</h1>';
    echo '<p>Desde aquí puedes gestionar los clientes y transacciones.</p>';
    echo '<ul>
            <li><a href="' . admin_url('admin.php?page=deae_customers') . '">📋 Ver Clientes</a></li>
            <li><a href="' . admin_url('admin.php?page=deae_transactions') . '">💳 Ver Transacciones</a></li>
          </ul>';
    echo '</div>';
}