<?php

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

class ArticlePageController extends PageController
{
    private static $allowed_actions = array(
        'CommentForm',
    );

    public function CommentForm()
    {
        $form = Form::create(
            $this,
            __FUNCTION__,
            FieldList::create(
                TextField::create('Name', ''),
                EmailField::create('Email', ''),
                TextareaField::create('Comment', '')
            ),
            FieldList::create(
                FormAction::create('handleComment', 'Post Comment')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-default-color btn-lg')
            ),
            RequiredFields::create('Name', Email::class, 'Comment')
        )->addExtraClass('form-style');

        foreach ($form->Fields() as $field) {
            $field->addExtraClass('form-control')
                ->setAttribute('placeholder', $field->getName() . '*');
        }

        $data = $this->getRequest()->getSession()->get("FormData.{$form->getName()}.data");

        return $data ? $form->loadDataFrom($data) : $form;
    }


    public function handleComment($data, $form)
    {
        $this->getRequest()->getSession()->set("FormData.{$form->getName()}.data", $data);
        $existing = $this->Comments()->filter(array(
            'Comment' => $data['Comment']
        ));
        if ($existing->exists() && strlen($data['Comment']) > 20) {
            $form->sessionMessage('That comment already exists! Spammer!', 'bad');

            return $this->redirectBack();
        }

        $this->notifyOfNewComment();

        $comment = ArticleComment::create();
        $comment->ArticlePageID = $this->ID;
        $form->saveInto($comment);
        $comment->write();

        $this->getRequest()->getSession()->clear("FormData.{$form->getName()}.data");
        $form->sessionMessage('Thanks for your comment', 'good');

        return $this->redirectBack();
    }

    /**
     * Notify all respondents that there's been a new comment!
     */
    private function notifyOfNewComment()
    {
        // Gather distinct emails to notify from the Article Page.
        $emails = $this->Comments()->map("Email");

        // get mailer
        if ($emails->count() > 1) {
            foreach ($emails as $toEmail => $author) {
                /**
                 * @var Email $email
                 */
                $email = Injector::inst()->create('SilverStripe\Control\Email\Email');
                $email->setFrom('admin@one-ring-rentals.com');
                $email->setTo($toEmail);
                $email->setSubject('New reply to your comment!');
                $email->setHTMLTemplate('Email/article-comment-reply.ss');
                $email->setData([
                    'Name' => $author,
                    'Title' => $this->data()->Title,
                    'Link' => $this->Link()
                ]);
                $email->send();
            }
        }
    }
}