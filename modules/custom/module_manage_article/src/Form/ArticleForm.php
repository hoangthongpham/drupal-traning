<?php
    namespace Drupal\module_manage_article\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Code\Database\Database;
    use Drupal\Core\Entity\ContentEntityForm;
    use Drupal\node\Entity\Node;
    use \Drupal\file\Entity\File;

    class ArticleForm extends FormBase {
        /**
         * {@inheritdoc}
         */
        
        public function getFormId(){
            return 'add_article';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form,FormStateInterface $form_state ){
            $form['title'] = array(
                '#type'=>'textfield',
                '#title'=>t('Title'),
                '#default_value'=>'',
                '#required' => true,
            );
            $form['main']['image'] = array(
                '#type' => 'text_format',
                '#title' => t('image'),
                '#default_value' => '',
            );
            $form['body_value'] = array(
                '#type'=>'textarea',
                '#title'=>'Body',
                '#default_value'=>'',
                '#required' => true,
            );
            $form['save'] = array(
                '#type'=>'submit',
                '#value'=>'Save',
                '#button_type'=> 'primary'
            );

            return $form;

        }
        /**
         * {@inheritdoc}
         */
        public function validateForm(array &$form, FormStateInterface $form_state){
            $title =$form_state->getValue('title');
            if(trim($title) == ''){
                $form_state->setErrorByName('title',$this->t('Title field is required'));
            }
            elseif($form_state->getValue('body_value') ==0){
                $form_state->setErrorByName('body_value',$this->t('Body field is required'));
            }
        }
        /**
         * {@inheritdoc}
         */
        public function submitForm (array &$form, FormStateInterface $form_state ){
            $postData = $form_state->getValues();

            unset($postData['save'], $postData['form_build_id'], $postData['form_token'], $postData['form_id'], $postData['op']);
            
            $node = Node::create(array(
                'type' => 'article',
                'title' => $postData['title'],
                'body_value' => $postData['body_value'],
                'langcode' => 'en',
                'uid' => '1',
                'status' => 1,
                'body' => [
                    'value' => $postData['body_value'],
                    'format' => 'basic_html',
                ],
            ));
            $node->save();
            
            \Drupal::messenger()->addStatus(t('Article data save successfully!'), 'status',TRUE);
        }

    }

