<p><b>User-Agent:</b> <?= $request->header("User-Agent") ?></p>

<?php if(isset($params)): ?>
<p><b>Params:</b> <?= json_encode($params) ?></p>
<?php endif; ?>
