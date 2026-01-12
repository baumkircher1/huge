<div class="container">
    <h1>Chat mit <?= $this->other_user ? htmlentities($this->other_user->user_name) : 'Unbekannt'; ?></h1>

    <div class="box">
        <a href="<?= Config::get('URL'); ?>messanger" class="btn btn-sm mb-3">
            <i class="fa-solid fa-arrow-left"></i> Zurück
        </a>

        <?php if (!$this->other_user): ?>
            <p>Benutzer nicht gefunden.</p>
        <?php else: ?>

            <!-- Nachrichten -->
            <div class="mb-4">
                <?php if ($this->messages): ?>
                    <?php foreach($this->messages as $msg): ?>
                        <?php if ($msg->sender_id == $this->my_id): ?>
                            <!-- Meine Nachricht -->
                            <div class="text-end">
                                <strong>Ich:</strong> <?= htmlentities($msg->message_text); ?>
                            </div>
                        <?php else: ?>
                            <!-- Nachricht vom anderen Benutzer -->
                            <div class="text-start">
                                <strong><?= htmlentities($this->other_user->user_name); ?>:</strong> <?= htmlentities($msg->message_text); ?>
                            </div>
                        <?php endif; ?>
                        <hr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Noch keine Nachrichten.</p>
                <?php endif; ?>
            </div>

            <!-- Nachricht senden -->
            <form method="post" action="<?= Config::get('URL'); ?>messanger/send">
                <input type="hidden" name="receiver_id" value="<?= $this->other_user->user_id; ?>">
                <textarea name="message_text" class="form-control mb-2" rows="3" placeholder="Nachricht schreiben..." required></textarea>
                <button type="submit" class="btn btn-primary">Senden</button>
            </form>

        <?php endif; ?>
    </div>
</div>
