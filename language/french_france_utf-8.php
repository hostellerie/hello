<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | french_france_utf-8.php
// |                                                                           |
// | Geeklog hello plugin file                                                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2016-2026 by the following authors:                         |
// |                                                                           |
// | Authors: ::Ben - ben AT geeklog DOT fr                                    |
// +---------------------------------------------------------------------------+
// | Created with the Geeklog Plugin Toolkit.                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

// Language for plugin users
$LANG_HELLO01 = array(
    'plugin_name'             => 'Hello',
    'overview'                => 'Le plugin Hello vous permet de composer, planifier et envoyer des campagnes d\'e-mails HTML ciblées à vos groupes d\'utilisateurs. Il dispose de capacités CRM complètes, y compris la personnalisation de variables, le suivi des ouvertures et des clics, et la conformité RFC 8058 One-Click Unsubscribe. Le plugin gère également les quotas d\'envoi horaires automatiquement pour protéger la réputation de votre serveur.',
	'email_save'              => 'E-mail Enregistré',
	'email_schedule'          => 'e-mail(s) planifié(s).',
	'ready_to_send'           => '(prêts à être envoyés)',
    'email_sent'              => 'e-mail(s) envoyé(s)',
    'why_email'               => 'Vous recevez cet email en tant que membre de %s',
    'sender'                  => 'Expéditeur',
    'unsubscribe'             => 'Se désinscrire',
    'unsub_confirm_msg'       => 'Confirmez que vous ne souhaitez plus recevoir ces e-mails.',
    'unsub_confirm_btn'       => 'Confirmer la désinscription',
    'email_fail'              => 'email(s) échoué(s)',
	'menu_label'              => 'Menu',
	'homepage_label'          => 'Hello',
	'send_email_group'        => 'Nouveau',
    'manual'                  => 'Attente',
	'manual_intro'            => 'Cette page vous permet de déclencher manuellement l\'envoi de la file d\'attente si vous n\'avez pas configuré de tâche cron sur votre serveur. Cliquez sur le bouton ci-dessous pour traiter le prochain lot d\'emails.',
	'configuration'           => 'Config',
	'read_email'              => 'Campagnes',
    'bounces'                 => 'Bounces',
	'see_email'               => 'Voir',
	'id'                      => 'ID',
	'subjet'                  => 'Sujet',
    'creation'                => 'Date',
    'group'                   => 'Groupe/Source',
    'quantity'                => 'Quantité',
	'email_display'           => '',
	'email'                   => 'Email',
    'max'                     => 'max (voir configuration)',
    'send_next'               => 'Traiter la suite maintenant',
	'import_message'          => 'Sélectionnez le groupe d\'utilisateurs à cibler. Les membres s\'étant désabonnés seront automatiquement exclus de cet envoi.',
	'select_file'             => 'Choisir un fichier .csv',
	'csv_file'                => 'fichier csv',
	'separator'               => 'Sélectionner le délimiteur',
	'hello_sent'              => 'Hello envoyé à',
    'cron_title'              => 'Configuration de la tâche planifiée (CRON)',
    'cron_desc'               => 'Pour automatiser l\'envoi de la file d\'attente des e-mails, ajoutez la commande suivante au planificateur de tâches (CRON) de votre serveur.<br/><i>Note : Selon votre hébergeur (ex: cPanel), le chemin vers l\'exécutable PHP peut être <code>/usr/local/bin/php -q</code> ou <code>php-cli</code> au lieu de <code>/usr/bin/php</code>.</i>',
    'cron_note'               => 'Exécutez ce script en ligne de commande (CLI). Vous n\'avez plus besoin de clé secrète car il est impossible de l\'exécuter depuis un navigateur web.',
	'contacts'                => 'contacts',
    'crm_dashboard'           => 'CRM',
    'back_to_list'            => 'Retour à la liste',
    'back_to_campaign'        => 'Retour à la campagne',
    'back_to_campaign_stats'  => 'Retour aux statistiques de la campagne',
    'back_to_subscribers'     => 'Retour à la liste des abonnés',
    'emails_received_by'      => 'Emails reçus par ',
    'emails_opened_by'        => 'Emails ouverts par ',
    'status_updated_success'  => 'Statut mis à jour avec succès.',
    'emails_received'         => 'Emails Reçus',
    'force_unsubscribe'       => 'Forcer Désabonnement',
    'resubscribe'             => 'Réabonner',
    'col_username'            => 'Utilisateur',
    'col_email'               => 'Email',
    'col_subscription'        => 'Abonnement',
    'col_opens'               => 'Ouvertures',
    'col_clicks'              => 'Clics',
    'col_action'              => 'Action',
	'mdigest'                 => 'Notifications nouveaux articles',
	'ddigest'                 => 'Résumé quotidien',
    'doc_title'               => 'Documentation & Utilisation du plugin Hello',
    'doc_send_title'          => 'Comment envoyer une campagne',
    'doc_send_body'           => '<ul><li>Allez dans l\'onglet <strong>Nouvel email</strong>.</li><li>Sélectionnez le groupe cible. Les utilisateurs désabonnés seront automatiquement ignorés.</li><li>Rédigez votre message. Vous pouvez utiliser du HTML si l\'éditeur avancé est activé.</li><li>Cliquez sur Envoyer. Les messages sont d\'abord placés dans une <strong>file d\'attente</strong>.</li></ul>',
    'doc_crm_title'           => 'Dashboard CRM et Analyses (Abonnés, Ouvertures, Clics, Bounces)',
    'doc_crm_body'            => '<p>Le plugin inclut un système d\'analyse complet :</p>
                                  <ul>
                                  <li><strong>CRM Dashboard (Abonnés) :</strong> Affiche tous les utilisateurs, le nombre d\'emails qu\'ils ont reçus, ouverts, et cliqués. Vous pouvez forcer l\'abonnement ou le désabonnement d\'un membre, et cliquer sur les chiffres pour voir le détail des emails.</li>
                                  <li><strong>Statistiques des Campagnes :</strong> La page des archives affiche pour chaque campagne le taux d\'ouverture et le taux de clic. Vous y trouverez des tableaux détaillés listant exactement qui a ouvert et qui a cliqué sur quel lien.</li>
                                  <li><strong>Gestion des Bounces :</strong> L\'onglet Bounces vous permet de désabonner rapidement et en masse les adresses emails revenues en erreur, en collant simplement une liste d\'emails.</li>
                                  </ul>',
    'doc_support_link'        => 'Lien du support',
    'stat_campaigns'          => 'Campagnes',
    'stat_sent'               => 'Emails Envoyés',
    'stat_open_rate'          => 'Taux d\'ouverture',
    'stat_click_rate'         => 'Taux de clic',
    'stat_members'            => 'Membres',
    'stat_emails'             => 'Avec Email',
    'stat_subscribed'         => 'Abonnés',
    'test_sent'               => 'Message de test envoyé à votre adresse e-mail (si des articles sont disponibles) !',
    'btn_test'                => 'Envoyer un Test',
    'btn_test_title'          => 'Envoyer uniquement à votre adresse pour vérifier le rendu',
    'queue_empty'             => 'File d\'attente vide (Aucun e-mail en attente d\'envoi)',
    'queue_status'            => 'État de la file d\'attente',
    'queue_remaining'         => 'e-mails restants en file d\'attente',
    'queue_sent_pct'          => '% envoyés',
    'top_readers'             => 'Top 5 Lecteurs (Ouvertures)',
    'top_opens'               => 'ouvertures',
    'top_no_data'             => 'Pas encore assez de données.',
    'top_domains'             => 'Top 5 Domaines',
    'bounces_title'           => 'Gestion des Bounces (Retours)',
    'bounces_desc'            => 'Importez ici les adresses emails en erreur pour les désabonner.',
    'bounces_success'         => '%d utilisateur(s) désabonné(s) avec succès.',
    'bounces_warning'         => '%d adresse(s) email introuvable(s) dans la base.',
    'bounces_empty'           => 'Aucune adresse email valide n\'a été trouvée dans votre saisie.',
    'bounces_import_title'    => 'Import Manuel des Bounces',
    'bounces_import_desc'     => 'Collez ci-dessous la liste des adresses emails qui vous sont revenues en erreur (Hard Bounces). Vous pouvez séparer les adresses par une <strong>virgule</strong> ou faire un <strong>retour à la ligne</strong>.',
    'bounces_btn'             => 'Désabonner ces utilisateurs',
    'status_actions'          => 'Statut / Actions',
    'status_active'           => 'En cours',
    'status_paused'           => 'En pause',
    'status_finished'         => 'Terminée',
    'action_pause'            => 'Pause',
    'action_resume'           => 'Reprendre',
    'action_stop'             => 'Stop',
    'action_stop_confirm'     => 'Voulez-vous vraiment supprimer les emails restants pour cette campagne ? Cette action est définitive.',
    'emails_remaining'        => 'restants',
    'doc_config_title'        => 'Options de Configuration',
    'doc_config_body'         => '<ul><li><strong>max_email</strong> : Nombre maximum d\'e-mails envoyés par lot (pour protéger la réputation de votre serveur).</li><li><strong>email_per_hour</strong> : Nombre maximum d\'e-mails envoyés par heure.</li><li><strong>sleep_email</strong> : Pause en secondes entre chaque e-mail envoyé dans un lot.</li><li><strong>keep_email</strong> : Nombre de jours avant suppression automatique des archives d\'e-mails (0 = ne jamais supprimer).</li></ul>',
    'doc_mdigest_title'       => 'Notifications Nouveaux Articles (Manual Digest)',
    'doc_mdigest_body'        => '<ul><li>Permet d\'envoyer manuellement un résumé des articles récents aux utilisateurs. Nécessite l\'activation de <code>$_CONF[\'emailstories\'] = 1;</code> dans la configuration de votre site.</li></ul>',
    'doc_ddigest_title'       => 'Maintenance Résumé Quotidien (Daily Digest)',
    'doc_ddigest_body'        => '<ul><li>Permet de réinitialiser ou gérer le planificateur du résumé quotidien.</li></ul>',
    'access_denied'           => 'Accès interdit',
    'access_denied_msg'       => 'Vous n\'avez pas accès à cette interface administrative.',
    'installation_failed'     => 'L\'installation a échoué.',
    'installation_failed_msg' => 'L\'installation du plugin Manual Digest a échoué. Merci de consulter le ficheir error.log pour plus d\'informations.',
    'uninstall_failed'        => 'La désinstallation a échoué.',
    'uninstall_failed_msg'    => 'La désinstallation du plugin Manual Digest a échoué. Merci de consulter le ficheir error.log pour plus d\'informations.',

    'digest_sent'             => 'La notification a bien été expédiée. <a href="' . $_CONF['site_admin_url'] . '/plugins/hello/index.php">Retour à l\'interface d\'administration</a>.',
    'digest_intro_email'      => 'Bonjour %s. Voici les derniers articles publiés sur le site %s.',
    'digest_intro'            => 'Cette fonction vous permet d\'informer les membres de votre site de la publication d\'un nouvel article. Elle est indépendante de la fonction automatisée par cronjob et vous permet d\'expédier une notification supplémentaire lorsque vous le souhaitez. Cliquez sur le bouton "Envoyer" ci-dessous (disponible uniquement si vous avez de nouveaux articles à signaler) pour expédier l\'avis de parution d\'un nouvel article aux membres qui le souhaitent.',
    'digest_last_sent'        => 'Dernière notification expédiée :',
    'never'                   => '(Jamais)',
    'no_stories'              => '<b>Aucun nouvel article trouvé.</b>',
    'num_stories'             => '<b>%d</b> articles seront expédiés.',
    'num_stories_digest'      => 'Nombre d\'articles expédiés : %d',
    'send_button'             => 'Envoyer!',
    'not_enabled1'            => '<strong>Attention:</strong> Le résumé quotidien n\'est pas activé. Assurez-vous que',
    'not_enabled2'            => 'dans l\'interface de configuration de l\'administrateur.',

    'search_text'             => 'Rechercher un nom de membre, une adresse email ou un identifiant d\'utilisateur.',
    'search_button'           => 'Rechercher',
    'new_search'              => 'Nouvelle recherche',
    'inspect_text'            => "Cliquez sur le nom de l'utilisateur pour inspecter ses paramètres de notification.",
    'uid_not_found'           => 'Il n\'y a pas de compte pour l\'identifiant %d.',
    'not_found'               => 'Aucun résultat pour <b>%s</b>.',
    'try_again'               => 'Merci d\'essayer à nouveau.',
    'user'                    => 'Membre',
    'topics'                  => 'Catégories',
    'all_topics'              => 'Toutes les catégories',
    'no_topics'               => 'Aucun',
    'reset_button'            => 'Reset',
    'success'                 => 'La notification pour l\'utilisateur <b>%s</b> a été supprimée.',
    'block_headline'          => 'Notification de publication',
    'digest_reset'            => 'La notification a été réinitialisée. <a href="' . $_CONF['site_admin_url'] . '/plugins/hello/index.php">Retour</a>.',
    'explain_reset'           => 'Utilisation avancée : Si vous ne souhaitez pas que système expédie de notification pour les derniers articles publiés, cliquez sur le bouton "Reset". Ceci aura pour effet d\'annuler d\'éventuelles notification par cronjob.',

    'forums'                  => 'Forums',
    'no_forums'               => 'Aucun',
    'forum_topics'            => 'sujets dans le forum',
    'inst_index'              => 'Gérer l\'envoi manuel du résumé des articles récents et rechercher des membres. La documentation complète du plugin se trouve au bas de cette page.',
    'inst_group'              => 'Rédigez et envoyez une nouvelle campagne d\'emails ciblée à un groupe d\'utilisateurs.',
    'inst_read'               => 'Consultez vos archives de campagnes et accédez aux statistiques détaillées.',
    'inst_crm'                => 'Analysez l\'engagement de vos abonnés, les taux d\'ouvertures et de clics.',
    'inst_manual'             => 'Vérifiez et expédiez manuellement la file d\'attente des emails planifiés.',
    'read_article'            => 'Lire l\'article : ',
    'unsub_error_title'       => 'Erreur de désinscription',
    'unsub_error_heading'     => 'Action impossible',
    'unsub_admin_error'       => 'L\'administrateur principal du site ne peut pas se désinscrire via ce lien. Veuillez modifier vos préférences directement dans votre compte.',
    'resub_title'             => 'Réinscription réussie',
    'resub_success'           => 'Vous avez été réinscrit avec succès. Vous recevrez à nouveau nos e-mails.',
    'unsub_title'             => 'Désinscription',
    'unsub_success_heading'   => 'Désinscription réussie',
    'unsub_success_msg'       => 'Votre désinscription a bien été prise en compte. Nous ne vous enverrons plus ces e-mails. <br><br><em>Sachez que vous pouvez à tout moment réactiver ces notifications dans les paramètres de votre compte.</em>',
    'unsub_mistake'           => 'Vous avez cliqué par erreur ?',
    'resub_btn'               => 'Me réinscrire en un clic',
    'invalid_link_title'      => 'Lien invalide',
    'invalid_link_msg'        => 'Lien de désinscription invalide ou expiré.',
    'test_email_success'      => 'E-mail de test envoyé avec succès',
    'test_email_footer'       => 'MESSAGE DE TEST — Le lien de désinscription ci-dessous est une simulation. Vous pouvez tester tout le parcours sans modifier les préférences d’aucun abonné.',
    'unsubscribe_test'        => 'Se désinscrire (Test)',
    'unsub_test_warning'      => 'MODE TEST — Il s’agit d’une simulation de désinscription. La confirmation ne modifiera ni votre abonnement ni vos préférences d’e-mail.',
    'unsub_test_success'      => 'MODE TEST — Le parcours de désinscription a été testé avec succès, mais aucun abonnement ni aucune préférence d’e-mail n’a été modifié.',
    'resub_test_success'      => 'MODE TEST — Le parcours de réinscription a été testé avec succès, mais aucun abonnement ni aucune préférence d’e-mail n’a été modifié.',
    'test_email_failed'       => 'L’e-mail de test n’a pas pu être envoyé. Vérifiez le journal d’erreurs de Geeklog et la configuration de la messagerie.',
    'manual_paused_note'      => 'Note : %d e-mails supplémentaires sont en pause ou programmés pour plus tard et ne seront pas envoyés maintenant.',
    'crm_unique_clicks'       => 'clic(s) unique(s) sur : ',
    'crm_old_campaign'        => 'Ancienne Campagne',
    'crm_old_campaign_desc'   => 'Les statistiques détaillées (ouvertures, clics) n\'étaient pas enregistrées pour les campagnes envoyées avant la version 2.2.0 du plugin. Cette campagne a été envoyée à %d abonnés.',
    'crm_click_details'       => 'Détail des Clics :',
    'crm_yes'                 => 'Oui',
    'crm_no'                  => 'Non',
    'crm_clicks_count'        => 'clic(s)',
    'sub_active'              => 'Actif',
    'sub_unsubscribed'        => 'Désabonné',
    'hourly_quota_reached'    => 'Quota horaire atteint (%d). Veuillez patienter jusqu\'à la prochaine heure pour en envoyer davantage.',
    'crm_unique_clickers'     => 'Clics uniques : ',
    'crm_unsubs'              => 'Désabonnements : ',
    'crm_view_table'          => 'Voir le tableau détaillé des clics pour cette campagne',
    'crm_detail_campaign'     => 'Détail des clics pour la campagne #',
    'crm_user_history'        => 'Historique d\'engagement pour l\'utilisateur #',
    'crm_user'                => 'Utilisateur',
    'crm_email'               => 'Email',
    'crm_clicked_url'         => 'URL Cliquée',
    'crm_click_date'          => 'Date du clic',
    'crm_campaign_id'         => 'Campagne (ID)',
    'crm_subject'             => 'Sujet',
    'crm_campaign'            => 'Campagne',
    'crm_send_date'           => 'Date d\'envoi',
    'crm_opened'              => 'Ouverte',
    'crm_click'               => 'Clic',
    'crm_no_clicks'           => 'Aucun clic enregistré pour le moment.',
    'crm_campaign_stats'      => 'Statistiques de la campagne :',
    'crm_sent'                => 'Envoyé à',
    'crm_subscribers'         => 'abonnés',
    'crm_unique_opens'        => 'Ouvertures uniques',
    'crm_view_opens'          => 'Voir le tableau détaillé des ouvertures pour cette campagne',
    'crm_detail_opens'        => 'Détail des ouvertures pour la campagne #'
);

$LANG_configsections['hello'] = array(
    'label' => 'Hello',
    'title' => 'Hello Configuration'
);

$LANG_confignames['hello'] = array(
    'max_email' => 'Nombre d\'emails à envoyer par exécution <abbr title="Nombre maximal de messages traités lors d’un passage de la file d’envoi. Il s’agit de la taille d’un lot, et non du plafond horaire.">?</abbr>',
    'hourly_limit' => 'Plafond maximum horaire (sécurité/throttling) <abbr title="Limite de sécurité du nombre total de newsletters envoyées pendant une heure. Hello réduit automatiquement la taille du lot lorsque ce plafond approche. Utilisez 0 uniquement si vous souhaitez volontairement désactiver cette limite horaire.">?</abbr>',
    'track_clicks' => 'Suivre les clics avec des liens courts du site <abbr title="Remplace les liens éligibles par des liens courts du site afin de comptabiliser les clics. Désactivez cette option pour conserver les URL directes et ne pas enregistrer de statistiques de clics.">?</abbr>',
    'track_opens' => 'Suivre les ouvertures avec un pixel 1×1 <abbr title="Ajoute une petite image invisible pour estimer les ouvertures. Ces statistiques restent approximatives car certains logiciels bloquent les images ou utilisent des relais de confidentialité.">?</abbr>',
);

$LANG_configsubgroups['hello'] = array(
    'sg_0' => 'Paramètres principaux',
);

$LANG_fs['hello'] = array(
    'fs_01' => 'Hello plugin'
);

$LANG_configselects['hello'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE)
);
?>