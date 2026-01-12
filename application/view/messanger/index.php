<div class="container">
    <h1>Messenger</h1>
    <div class="box">

        <?php if ($this->users): ?>
            <ul>
                <?php foreach($this->users as $user): ?>
                    <li>
                        <a href="<?= Config::get('URL'); ?>messanger/chat/<?= $user->user_id; ?>">
                            <?= htmlentities($user->user_name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Keine Benutzer gefunden.</p>
        <?php endif; ?>

    </div>
</div>
