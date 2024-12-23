<?php
    include_once '../src/backend/backend.php';
    
    $redirectUrl = 'bibliotecario.php?tab=lettori';

    try {
        $lettori = getLettori();
    } catch (ErroreInternoDatabaseException $e) {
        redirect_to('internal_error.php');
    }
?>

<?php if (empty($lettori)): ?>
<div class="alert alert-warning text-center" role="alert">
    Non ci sono lettori
</div>
<?php else: ?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Codice Fiscale</th>
      <th scope="col">Nome</th>
      <th scope="col">Cognome</th>
      <th scope="col">Email</th>
      <th scope="col">Categoria</th>
      <th scope="col"></th>
      <th scope="col">Ritardi</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($lettori as $lettore): ?>
      <tr>
        <th scope='row'><?= $lettore['codice_fiscale'] ?></th>
        <td><?= $lettore['nome'] ?></td>
        <td><?= $lettore['cognome'] ?></td>
        <td><?= $lettore['email'] ?></td>
        <td><?= $lettore['categoria'] ?></td>
        <td>
          <a class="btn btn-light" data-bs-toggle="collapse" 
             href="#collapse<?= $lettore['codice_fiscale'] ?>" role="button">
            Cambia categoria
          </a>
        </td>
        <td><?= $lettore['ritardi'] ?></td>
        <td>
          <form action="<?= $redirectUrl ?>" method="POST">
            <input type="hidden" name="codice_fiscale" value="<?= $lettore['codice_fiscale'] ?>">
            <input type="hidden" name="operazione" value="<?= TipoOperazione::AZZERA_RITARDI_LETTORE->value ?>">
            <button type="submit" class="btn btn-warning">Azzera ritardi</button>
          </form>
        </td>
      </tr>
      <tr class="collapse" id="collapse<?= $lettore['codice_fiscale'] ?>">
        <td colspan="8">
          <form action="<?= $redirectUrl ?>" method="POST">
            <div class="row">
              <div class="col-9">
                <input type="hidden" name="codice_fiscale" value="<?= $lettore['codice_fiscale'] ?>">
                <input type="hidden" name="operazione" value="<?= TipoOperazione::CAMBIA_CATEGORIA_LETTORE->value ?>">
                <select class="form-select <?= isset($errors[TipoOperazione::CAMBIA_CATEGORIA_LETTORE->value]['categoria']) ? 'border-danger' : '' ?>" 
                        id="categoria" name="categoria">
                  <?php foreach (Categoria::cases() as $categoria): ?>
                    <option value="<?= $categoria->value ?>"
                        <?php if (isset($inputs[TipoOperazione::CAMBIA_CATEGORIA_LETTORE->value]['categoria']) && 
                                        $inputs[TipoOperazione::CAMBIA_CATEGORIA_LETTORE->value]['categoria'] == $categoria->value) 
                                echo 'selected' 
                        ?>>
                      <?= $categoria->toString() ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-3">
                <button type="submit" class="btn btn-primary">Cambia</button>
              </div>
            </div>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<form action="<?= $redirectUrl ?>" method="POST" class="mt-4">
  <div class="row mb-2">
    <div class="col">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['nome']) ? 'border-danger' : '' ?>" id="nome" name="nome" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['nome'] ?? '' ?>">
    </div>
    <div class="col">
      <label for="password1" class="form-label">Password</label>
      <input type="password" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['password1']) ? 'border-danger' : '' ?>" id="password1" name="password1" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['password1'] ?? '' ?>">
    </div> 
  </div>
  <div class="row mb-2">
    <div class="col">
      <label for="cognome" class="form-label">Cognome</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['cognome']) ? 'border-danger' : '' ?>" id="cognome" name="cognome" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['cognome'] ?? '' ?>">
    </div> 
    <div class="col">
      <label for="password2" class="form-label">Conferma Password</label>
      <input type="password" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['password2']) ? 'border-danger' : '' ?>" id="password2" name="password2" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['password2'] ?? '' ?>">
    </div> 
  </div>
  <div class="row mb-2">
    <div class="col-6">
      <label for="codice_fiscale" class="form-label">Codice Fiscale</label>
      <input type="text" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['codice_fiscale']) ? 'border-danger' : '' ?>" id="codice_fiscale" name="codice_fiscale" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['codice_fiscale'] ?? '' ?>">
    </div> 
  </div>
  <div class="row mb-2">
    <div class="col-6">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['email']) ? 'border-danger' : '' ?>" id="email" name="email" value="<?= $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['email'] ?? '' ?>">
    </div> 
  </div>
  <div class="row mb-2">
    <div class="col-6">
      <label for="categoria" class="form-label">Categoria</label>
      <select class="form-select <?= isset($errors[TipoOperazione::AGGIUNGI_LETTORE->value]['categoria']) ? 'border-danger' : '' ?>" id="categoria" name="categoria">
      <?php foreach (Categoria::cases() as $categoria): ?>
        <option value="<?= $categoria->value ?>" <?= (isset($inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['categoria']) && $inputs[TipoOperazione::AGGIUNGI_LETTORE->value]['categoria'] == $categoria->value) ? 'selected' : '' ?>><?= $categoria->toString() ?></option>
      <?php endforeach; ?>
      </select>
    </div> 
  </div>
  <input type="hidden" name="operazione" value="<?= TipoOperazione::AGGIUNGI_LETTORE->value ?>">
  <?php 
    $aggiungiErrors = $errors[TipoOperazione::AGGIUNGI_LETTORE->value] ?? [];
    $aggiungiErrors = array_filter($aggiungiErrors, fn($error) => !is_null($error) && $error !== '');
  ?>
  <p class="text-danger <?= empty($aggiungiErrors) ? 'd-none' : '' ?>">
    <?= join('<br>', $aggiungiErrors) ?>
  </p>
  <button type="submit" class="btn btn-primary mt-4">Aggiungi lettore</button>
</form>
