<?php

namespace Drupal\rules_role_email\Plugin\RulesAction;

use Drupal;
use Drupal\node\NodeInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\user\Entity\User;

/**
 * Provides a 'RoleEmailAction' action.
 *
 * @RulesAction(
 *  id = "role_email_action",
 *  label = @Translation("Send an email to all users of a role"),
 *  category = @Translation("User"),
 *  context = {
 *    "roles" = @ContextDefinition("entity:user_role",
 *      label = @Translation("Roles"),
 *      multiple = TRUE
 *    ),
 *    "subject" = @ContextDefinition("string",
 *      label = @Translation("Subject"),
 *      description = @Translation("The email's subject."),
 *    ),
 *   "message" = @ContextDefinition("string",
 *     label = @Translation("Message"),
 *     description = @Translation("The email's message body."),
 *   ),
 *   "node" = @ContextDefinition("entity:node",
 *     label = @Translation("Node"),
 *     description = @Translation("Optionally enter in 'node' as a typed data parameter to use tokens."),
 *     default_value = "node",
 *     required = false
 *   )
 *  }
 * )
 */
class RoleEmailAction extends RulesActionBase {

  /**
   * Mail manager object so we can send emails.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * RoleEmailAction constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailManager = Drupal::service('plugin.manager.mail');
  }

  /**
   * Send email to users of specified roles.
   *
   * @param array $roles
   * @param string $subject
   * @param string $message
   * @param \Drupal\node\NodeInterface $node
   */
  protected function doExecute(array $roles, $subject, $message, $node = NULL) {
    $users = $this->retrieveUsersOfRoles($roles);

    // Enable token support if the user has provided a node context.
    if (isset($node) && $node instanceof NodeInterface) {
      $message = Drupal::token()->replace($message, ['node' => $node]);
    }

    // Send out each email individually because certain users may have
    // different preferred languages such as French or Spanish.
    foreach ($users as $user) {
      if ($user instanceof User) {
        $langcode = $user->getPreferredLangcode();
        $params = [
          'subject' => $this->t('@subject', ['@subject' => $subject]),
          'message' => $message,
        ];
        // Set a unique key for this mail.
        $key = 'rules_action_mail_' . $this->getPluginId();

        $mail = $user->get('mail')->getValue();
        $to = $mail[0]['value'];
        $this->mailManager->mail('rules', $key, $to, $langcode, $params, TRUE);
      }
    }
  }

  /**
   * Returns an array of user objects based on the specified roles.
   * Users that are blocked will NOT receive any email notifications.
   *
   * @param array $roles
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|static[]
   */
  protected function retrieveUsersOfRoles(array $roles) {
    $uids = Drupal::entityQuery('user')
      ->condition('roles', $roles, 'IN')
      ->condition('status', 1)
      ->execute();

    return User::loadMultiple($uids);
  }

}
