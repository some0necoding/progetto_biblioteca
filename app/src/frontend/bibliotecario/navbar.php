<?php
    include_once '../src/frontend/operazioni.php';
?>

<nav class="navbar navbar-expand-lg bg-primary">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
        <a class="nav-link <?= $active == 'autori' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=autori">Autori</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'libri' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=libri">Libri</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'sedi' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=sedi">Sedi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'copie' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=copie">Copie</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'bibliotecari' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=bibliotecari">Bibliotecari</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'lettori' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=lettori">Lettori</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $active == 'prestiti' ? 'active' : '' ?>" aria-current="page" href="<?= $page ?>?tab=prestiti">Prestiti</a>
        </li>
      </ul>
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
            <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
          </svg>
        </button>
        <ul class="dropdown-menu">
          <li>
          <a class="dropdown-item d-flex justify-content-between align-items-center" href="<?= $page ?>?tab=account">Account
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
              </svg>
            </a>
          </li>
          <li>
            <form action="bibliotecario.php" method="POST">
              <input type="hidden" name="operazione" value="<?= TipoOperazione::LOGOUT->value ?>">
              <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center">Logout
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                  <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                </svg>
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
