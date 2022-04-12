<?php 
    namespace Drupal\my_crud\Form;
    
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Core\Database\Database;
    use Drupal\Core\Messenger;
    use  Drupal\Core\Entity\ContentEntityType;
    use Drupal\Core\Ajax\HtmlCommand;
    use Drupal\Core\Ajax\AjaxResponse;
 

    class RegisterForm extends FormBase 
    {
        public function getFormId(){
            return 'register_form';
        }

        public function buildForm(array $form, FormStateInterface $form_state)
        {   
            $conn = Database::getConnection();
            $record = [];
            $state_options = static::getFirstDropdownOptions();
            if (empty($form_state->getValue('state_dropdown'))) {
                // Use a default value.
                  $selected_option = key($state_options);
                } 
            else {
                $selected_option = $form_state->getValue('state_dropdown');
                }
            if(isset($_GET['id']))
            {
                $query = $conn -> select('my_crud','m') -> condition('id', $_GET['id'])->fields('m');
                $record = $query->execute()->fetchAssoc();
            }

            $form['name'] =['#type'=>'textfield','#title' => t('Name:'),'#required'=>true,'#default_value'=> (isset($record['name']) ? $record['name'] : '')];

            $form['phone'] =['#type'=>'tel','#title' => t('Phone:'),'#required'=>true,'#default_value'=> (isset($record['phone']) ? $record['phone'] : '')];

            $form['email'] =['#type'=>'email','#title' => t('Email:'),'#required'=>true,'#default_value'=> (isset($record['email']) ? $record['email'] : '')];

            $form['option_state_fieldset'] = [
                '#type' => 'fieldset',
                '#title' => $this->t('Choose List Age'),
              ];

              $form['option_state_fieldset']['state_dropdown'] = [
                '#type' => 'select',
                '#title' => $this->t('List Age'),
                '#required'=>true,
                '#options' => $state_options,
                '#default_value' => $selected_option,
                // Bind an Ajax callback to the element.
                '#ajax' => [
                  'callback' => '::instrumentDropdownCallback',
                  'wrapper' => 'state-fieldset-container',
                  'event' => 'change',
                ],
              ];

              $form['select_fieldset_container'] = [
                '#type' => 'container',
                '#attributes' => ['id' => 'state-fieldset-container'],
              ];
            
              $form['select_fieldset_container']['select_fieldset'] = [
                '#type' => 'fieldset',
                '#title' => $this->t('Choose an one'),
              ];

              $form['select_fieldset_container']['select_fieldset']['select_dropdown'] = [
                '#type' => 'select',
                '#title' => $state_options[$selected_option] . ' ' . $this->t('Age'),
                '#required'=>true,
                '#options' => static::getSecondDropdownOptions($selected_option),
                '#default_value' => !empty($form_state->getValue('select_dropdown')) ? $form_state->getValue('select_dropdown') : '',
              ];
          
              $form['intro'] =['#type'=>'textarea','#title' => t('Introduce yourself:'),'#default_value'=> (isset($record['review']) ? $record['review'] : 'Introduce yourself')];
   

            $form['action'] = ['#type'=>'action',];

            $form['actions']['submit'] = array(
                '#type' => 'submit',
                '#value' =>t('Save'),        
              );

              if ($selected_option == 'none') {
                // Change the field title to provide user with some feedback on why the
                // field is disabled.
                $form['select_fieldset_container']['select_fieldset']['select_dropdown']['#title'] = $this->t('You must choose an state first.');
                $form['select_fieldset_container']['select_fieldset']['select_dropdown']['#disabled'] = TRUE;
                $form['select_fieldset_container']['select_fieldset']['submit']['#disabled'] = TRUE;
              }
               
              return $form;

        }

        public function instrumentDropdownCallback(array $form, FormStateInterface $form_state) {
            return $form['select_fieldset_container'];
          }

        public function validateForm(array &$form, FormStateInterface $form_state)
        {
            $name = $form_state -> getValue('name');
            if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                $form_state->setErrorByName('name', $this->t('Name must be in characters only'));
            }
            $phone = $form_state->getValue('phone');
            if (preg_match('/^[0-9]{11}+$/', $phone)) {
                // the format /^[0-9]{11}+$/ will check for phone number with 10 digits and only numbers
                $form_state->setErrorByName('phone', $this->t('phone number with 11 digits and only numbers or invalid format'));
            }
            

            $email = $form_state -> getValue('email');
            $temp_email = explode('@', $email);
            $string ="kyanon.digital";
            if ($temp_email[1] != $string) {
                $form_state->setErrorByName('email', $this->t("Invalid email format. please use @kyanon.digital"));
            }

            $age = $form_state-> getValue('select_dropdown');
            if ($age <= 18) {
                $form_state->setErrorByName('select_dropdown', $this->t("You are under 18 years old"));
            }
           
        }

        
        public function submitForm(array &$form, FormStateInterface $form_state)
        {
            $field = $form_state ->getValues();

            $name = $field['name'];
            $email = $field['email'];
            $phone = $field['phone'];
            $list_age = $form_state->getValue('state_dropdown');
            $intro = $form_state->getValue('intro');

            $age = $form_state->getValue('select_dropdown');

            if(isset($_GET['id']))
            {
                $field = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'list_age' => $list_age,
                    'age' => $age,
                    'intro' => $intro,
                );

                $query = \Drupal::database();
                $query->update('my_crud')->fields($field)->condition('id',$_GET['id'])->execute();
                $this -> messenger()->addMessage('Successfully updated record');
            }
            else
            {
                $field = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'list_age' => $list_age,
                    'age' => $age,
                    'intro' => $intro,
                );

                $query = \Drupal::database();
                $query->insert('my_crud')->fields($field)->execute();
                $this -> messenger()->addMessage('Successfully save record');
            }
        }

        public static function getFirstDropdownOptions() {
            return [
                'none' => 'none',
                '1' => '10-20',
                '2' => '21-30',
                '3' => '31-40',
                '4' => '41-50',
            ];
          }
        
          public static function getSecondDropdownOptions($key = '') {
              switch ($key) {
              case '1':
                $options = [
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                    '13' => '13',
                    '14' => '14',
                    '15' => '15',
                    '16' => '16',
                    '17' => '17',
                    '18' => '18',
                    '19' => '19',
                    '20' => '20',
                  ];
                break;
        
              case '2':
                $options = [
                  '21' => '21',
                  '22' => '22',
                  '23' => '23',
                  '24' => '24',
                  '25' => '25',
                  '26' => '26',
                  '27' => '27',
                  '28' => '28',
                  '29' => '29',
                  '30' => '30',
                ];
                break;
        
              case '3':
                $options = [
                  '31' => '31',
                  '32' => '32',
                  '33' => '33',
                  '34' => '34',
                  '35' => '35',
                  '36' => '36',
                  '37' => '37',
                  '38' => '38',
                  '39' => '39',
                  '40' => '40',
                ];
                break;
                case '4':
                    $options = [
                     '41'   => '41',
                     '42' => '42',
                     '43' => '43',
                     '44' => '44',
                     '45' => '45',
                     '46' => '46',
                     '47' => '47',
                     '48' => '48',
                     '49' => '49',
                     '50' => '50',
                    ];
                    break;
        
              default:
                $options = ['none' => 'none'];
                break;
            }
              return $options;
          }


    }

?>