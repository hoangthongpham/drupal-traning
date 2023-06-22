<?php
    namespace Drupal\module_manage_article\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\node\Entity\Node;
    use Drupal\taxonomy\Entity\Term;
    use Drupal\menu_link_content\Entity\MenuLinkContent;
    
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
            $folder = date('Y-m', time());
            $form['field_image'] = [
                '#type' => 'managed_file',
                '#title' => t('Add a new file'),
                '#upload_validators' => [
                    'file_validate_extensions' => ['gif png jpg jpeg'],
                    'file_validate_size' => [25600000],
                ],
                '#upload_location' => 'public://'.$folder.''
             ];
            $form['body_value'] = array(
                '#type'=>'text_format',
                '#title'=>'Body',
                '#default_value'=>'',
                '#required' => true,
            );
            $form['field_tags'] = array(
                '#type'=>'textfield',
                '#title'=>'Tags',
                '#default_value'=>'',
                '#description'=>'Enter a comma-separated list. For example: Amsterdam, Mexico City, "Cleveland, Ohio" ',
                '#required' => true,
            );
            $form['status'] = array(
                '#type'=>'checkbox',
                '#title' => t('Published'),
                '#default_value'=>'',
            );

            $form['save'] = array(
                '#type'=>'submit',
                '#value'=>'Save',
                '#button_type'=> 'primary'
            );
            $form['preview'] = array(
                '#type'=>'submit',
                '#value'=>'Preview',
                '#button_type'=> 'warning'
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
            // echo'<pre>';
            // print_r($postData);exit;
            if($postData['field_image']){
                $fileId = $postData['field_image'][0]; 
            }else{
                $fileId=null;
            }
            $new_term = Term::create([
                'vid' => 'tags',
                'name' => $postData['field_tags'],
            ]);
            $new_term->enforceIsNew();
            $new_term->save();
            $node = Node::create(array(
                'type' => 'article',
                'title' => $postData['title'],
                'langcode' => 'en',
                'uid' => '1',
                'status' => 1,
                'body' => $postData['body_value'],
                'field_image'=>[
                    'target_id' => $fileId,
                    'alt'=>'',
                    'title'=>''
                ],
                'field_tags'=>[
                    'target_id'=>$new_term->tid->value
                ],
                'status'=>$postData['status'],
                 
            ));       
            $node->save();
            $response  = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
            $response->send();
            \Drupal::messenger()->addStatus(t('Article data save successfully!'), 'status',TRUE);
        }

    }

