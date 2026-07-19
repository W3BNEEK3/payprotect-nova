<?php
/**
 * components/ui/_confirm-modal.php
 *
 * Include this ONCE per layout (app.php, admin.php, public.php, auth.php).
 * modal.js finds this shell by id and populates/shows/hides it — pages never
 * build their own modal markup, they call NovaModal.confirm({...}) in JS.
 */
?>
<div class="modal-overlay" id="novaModalOverlay" hidden>
  <div class="modal-dialog" role="alertdialog" aria-modal="true" aria-labelledby="novaModalTitle" aria-describedby="novaModalBody">
    <div class="modal-header">
      <div class="modal-icon-badge" id="novaModalIconBadge">
        <span class="material-symbols-outlined" id="novaModalIcon">help</span>
      </div>
      <button type="button" class="modal-close" id="novaModalClose" aria-label="Close">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <h2 class="modal-title" id="novaModalTitle"></h2>
    <p class="modal-body" id="novaModalBody"></p>
    <div class="modal-actions">
      <button type="button" class="btn btn-secondary" id="novaModalCancel">Cancel</button>
      <button type="button" class="btn btn-primary" id="novaModalConfirm">Confirm</button>
    </div>
  </div>
</div>
