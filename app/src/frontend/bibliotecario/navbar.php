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
    </div>
  </div>
</nav>
