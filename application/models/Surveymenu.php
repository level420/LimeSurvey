<?php

/**
 * This is the model class for table "{{surveymenu}}".
 *
 * The followings are the available columns in table '{{surveymenu}}':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $survey_id
 * @property integer $user_id
 * @property integer $ordering
 * @property integer $level
 * @property string $title
 * @property string $description
 * @property string $changed_at
 * @property integer $changed_by
 * @property string $created_at
 * @property integer $created_by
 * @property integer $active
 *
 * The followings are the available model relations:
 * @property SurveymenuEntries[] $surveymenuEntries
 */
class Surveymenu extends LSActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{surveymenu}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('changed_at', 'required'),
			array('parent_id, survey_id, user_id, ordering, level, changed_by, created_by', 'numerical', 'integerOnly'=>true),
			array('title, position', 'length', 'max'=>255),
			array('description, created_at', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, parent_id, survey_id, user_id, ordering, level, position, title, description, changed_at, changed_by, created_at, created_by', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'surveymenuEntries' => array(self::HAS_MANY, 'SurveymenuEntries', 'menu_id'),
			'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
			'user' => array(self::BELONGS_TO, 'User', 'uid'),
			'parent' => array(self::BELONGS_TO, 'Surveymenu', 'parent_id'),
		);
	}

    
    public static function staticAddMenu($menuArray)
    {
        $oSurveymenu = new Surveymenu();
        $oSurveymenu->parent_id = $menuArray['parent_id'];
        $oSurveymenu->title = $menuArray['title'];
        $oSurveymenu->position = $menuArray['position'];
        $oSurveymenu->description = $menuArray['description'];

        $oSurveymenu->changed_at = date('Y-m-d H:i:s');
        $oSurveymenu->changed_by = Yii::app()->user->getId();
        $oSurveymenu->created_at = date('Y-m-d H:i:s');
        $oSurveymenu->created_by = Yii::app()->user->getId();

        $oSurveymenu->save();
        return $oSurveymenu->getPrimaryKey();
    }


	public function getMenuIdOptions (){
		$oSurveymenus = Surveymenu::model()->findAll();
		$options = [
			'' => gT('No parent menu')
		];
		foreach($oSurveymenus as $oSurveymenu){
			//$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
			$options[''.($oSurveymenu->id).''] = '('.$oSurveymenu->id.') '.$oSurveymenu->title;
		}
		//return join('\n',$options);
		return $options;
	}

	public function getSurveyIdOptions (){
		$oSurveys = Survey::model()->findAll('expires < :expire',['expire' => date('Y-m-d H:i:s', strtotime('+1 hour'))]);
		$options = [
			NULL => gT('All surveys')
		];
		foreach($oSurveys as $oSurvey){
			//$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
			$options[$oSurvey->sid] = $oSurvey->defaultlanguage->surveyls_title;
		}
		//return join('\n',$options);
		return $options;
	}

	public function getNextOrderPosition(){
		$oSurveymenus = Surveymenu::model()->findAll('parent_id=:parent_id',array('parent_id'=>0));
		return count($oSurveymenus);
	}

	public function getOrderOptions (){
		$oSurveymenus = Surveymenu::model()->findAll();
		$options = [];
		for($i=0; $i<=count($oSurveymenus); $i++){
			$options[$i] = $i;
		}
		//return join('\n',$options);
		return $options;
	}
	public function getPositionOptions (){
		$options = [
			'side' => gT('Sidemenu'),
			'collapsed' => gT('Collapsed menu'),
			'top' => gT('Top bar'),
			'bottom' => gT('Bottom bar')
		];
		//return join('\n',$options);
		return $options;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id'		=> gT('Parent'),
			'survey_id'		=> gT('Survey'),
			'user_id' 		=> gT('User'),
			'ordering' 		=> gT('ordering'),
			'level' 		=> gT('Level'),
			'title' 		=> gT('Title'),
			'position' 		=> gT('Position'),
			'description'	=> gT('Description'),
			'changed_at'	=> gT('Changed At'),
			'changed_by'	=> gT('Changed By'),
			'created_at'	=> gT('Created At'),
			'created_by'	=> gT('Created By'),
		);
	}
	public function getButtons(){
		$buttons = "<div style='white-space: nowrap'>";
        $raw_button_template = ""
            . "<button class='btn btn-default btn-xs %s %s' role='button' data-toggle='tooltip' title='%s' onclick='return false;'>" //extra class //title
            . "<i class='fa fa-%s' ></i>" //icon class
            . "</button>";
		
		if(Permission::model()->hasGlobalPermission('settings', 'update')){

			$deleteData = array(
				'action_surveymenu_deleteModal',
				'text-danger',
				gT("Delete this surveymenu"),
				'trash text-danger'
			);

			$buttons .= vsprintf($raw_button_template, $deleteData);

			$editData = array(
				'action_surveymenu_editModal',
				'text-danger',
				gT("Delete this surveymenu"),
				'edit'
			);

			$buttons .= vsprintf($raw_button_template, $editData);
		}

		$buttons .= '</div>';
		
		return $buttons;
	}
	/**
     * @return array
     */
    public function getColumns(){
        $cols = array(
			array(
			'name' => 'id',
			'value' => '\'<input type="checkbox" name="selectMenuToEdit" class="action_selectthismenu" value="\'.$data->id.\'" />\'',
			'type' => 'raw'
			),
			array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                "filter" => false
            ),
			array(
				'name' => 'title',
			),
			array(
				'name' => 'description',
			),
			array(
				'name' => 'ordering',
			),
			array(
				'name' => 'level',
			),
			array(
				'name' => 'position',
			),
			array(
				'name' => 'parent_id',
				'value' => '$data->parent_id ? $data->parent->title." (".$data->parent_id.")" : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			),
			array(
				'name' => 'survey_id',
				'value' => '$data->survey_id ? $data->survey->defaultlanguage->surveyls_title : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			),
			array(
				'name' => 'user_id',
				'value' => '$data->user_id ? $data->user->full_name : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			),
			// array(
			// 	'name' => 'changed_at',
			// ),
			// array(
			// 	'name' => 'changed_by',
			// ),
			// array(
			// 	'name' => 'created_at',
			// ),
			// array(
			// 	'name' => 'created_by',
			// ),
		);

		return $cols;
	}

	public function onBeforeSave($event){
		if($this->parent_id){
			$parentMenu = Surveymenu::model()->findByPk($this->parent_id);
			$this->level = (( (int) $parentMenu->level)+1);
		}
		return parent::onBeforeSave($event);
	}

	public function onAfterSave($event){
		$criteria = new CDbCriteria();
		
		$criteria->addCondition(['position=:position']);
		$criteria->addCondition(['ordering=:ordering']);
		$criteria->addCondition(['id!=:id']);
		$criteria->params = ['position' => $this->position, 'ordering' => (int) $this->ordering, 'id'=> (int) $this->id];
		$criteria->limit = 1;
		
		$collidingMenu = Surveymenu::model()->find($criteria);

		if($collidingMenu != null){
			$collidingMenu->ordering = (((int) $collidingMenu->ordering)+1);
			$collidingMenu->save();
		}
		return parent::onAfterSave($event);

	}

	/**
     * @return array
     */
    public function getShortListColumns(){
        $cols = array(
			array(
			'name' => 'id',
			),
			array(
				'name' => 'title',
			),
			array(
				'name' => 'description',
			),
			array(
				'name' => 'ordering',
			),
			array(
				'name' => 'position',
			),
			array(
				'name' => 'parent_id',
				'value' => '$data->parent_id ? $data->parent->title : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			),
			array(
				'name' => 'survey_id',
				'value' => '$data->survey_id ? $data->survey->defaultlanguage->surveyls_title : "<i class=\'fa fa-minus\'></i>"',
				'type' => 'raw'
			)
		);

		return $cols;
	}
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		//Don't show main menu when not superadmin
		if(Yii::app()->getConfig('demoMode') || !Permission::model()->hasGlobalPermission('superadmin','read'))
		{
			$criteria->compare('id','<> 1');
			$criteria->compare('id','<> 2');
		}

		$criteria->compare('id',$this->id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('survey_id',$this->survey_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('ordering',$this->ordering);
		$criteria->compare('level',$this->level);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('position',$this->position,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('changed_at',$this->changed_at,true);
		$criteria->compare('changed_by',$this->changed_by);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('created_by',$this->created_by);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Surveymenu the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
