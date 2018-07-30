import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

import * as globals from '../../app.globals';
import { AppService } from '../../app.service';
import { ActivitiesService } from '../activities.service';

import { Observable } from 'rxjs/Observable';
import 'rxjs/add/observable/forkJoin';

@Component({
  selector: 'add-activity',
  templateUrl: './add.component.html',
  styleUrls: ['./add.component.scss']
})
export class AddActivityComponent implements OnInit {

  @Input('activities') activities: any;
  @Output() updateEvent = new EventEmitter();
  @Output() closeEvent = new EventEmitter();

  showAddModal: boolean;
  types: any[];
  availableEntities = [];
  availableEntitiesBase = [];
  results: any[];
  filterEntity: string;
  form: any = [];
  step = 1;
  activityTypes: any[];
  entityForm: any;
  apiBaseUrl: string;
  getActivityFormUrl: string;
  module: any;

  constructor(
    private activityService: ActivitiesService,
    private sanitizer: DomSanitizer,
    private appService: AppService
  ) {
    this.apiBaseUrl = window['appConfig'].apiBaseUrl;
    this.getActivityFormUrl = window['appConfig'].getActivityFormUrl;
  }

  ngOnInit() {
    const that = this;

    setTimeout(() => {
      that.setAvailableTypes();
      that.setAvailableEntities();
    })
  }

  setAvailableTypes(): void {
    const activityTypes = this.activityService.getActivityTypes();

    Observable.forkJoin([activityTypes]).subscribe(results => {
      this.types = Object.keys(results[0]).map(function(key) { return results[0][key]; });
    });
  }

  setAvailableEntities(): void {
    const activityList = this.activityService.getActivityList();

    Observable.forkJoin([activityList]).subscribe(results => {
      this.availableEntitiesBase = Object.keys(results[0]).map(function(key) { return results[0][key]; });
    });
  }

  updateAvailableEntities(): void {
    const that = this;
    that.results = null;
    that.filterEntity = null;
    that.availableEntities = that.availableEntitiesBase;

    const type = that.types[that.form.type];
    const items = that.availableEntities.filter(entity => {
      return !type
          || type.bundle == entity.type
          && type.library == entity.library;
    }).map(entity => {
      return { entity };
    });

    that.form.existingEntity = null;
    that.availableEntities = items;

    setTimeout(() => {
      that.updateResults();
    });
  }

  updateResults() {

    const activitiesIds = this.activities.map(activity => activity.id);
    const formType = this.types[this.form.type];
    const formBundle = formType.bundle.trim().toUpperCase();

    // Filter by bundle && Prevent duplicate
    const results = this.availableEntities.filter(availableEntity => {
      const bundle = availableEntity.entity.type.trim().toUpperCase();
      const id = availableEntity.entity.activity_id;
      return bundle === formBundle && activitiesIds.indexOf(id) === -1;
    });

    // If text input value
    if (this.filterEntity !== null && this.filterEntity.length > 0) {
      const filter = this.filterEntity.trim().toUpperCase();
      this.results = results.filter(availableEntity => {
        const name = availableEntity.entity.name.toUpperCase();
        return name.indexOf(filter) !== -1;
      });
    } else {
      this.results = [];
    }
  }

  addActivityToModule(activity) {
    const addActivity = this.activityService.addActivity(this.module.entity_id, activity.entity.activity_id);

    Observable.forkJoin([addActivity]).subscribe(results => {
      this.updateEvent.emit(this.module);
      this.close();
    });
  }

  getAddForm() {
    const type = this.types[this.form.type];

    this.entityForm = this.sanitizer.bypassSecurityTrustResourceUrl(this.apiBaseUrl + this.appService.replaceUrlParams(this.getActivityFormUrl, { '%opigno_module': this.module.entity_id, '%type': type.bundle, '%item': '' }) + '?library=' + encodeURI(type.library));
    this.listenFormCallback();
  }

  listenFormCallback(): void {
    const that = this;

    var intervalId = setInterval(function() {
      if (typeof window['iframeFormValues'] !== 'undefined') {
        clearInterval(intervalId);

        const formValues = window['iframeFormValues'];
        delete window['iframeFormValues'];
        that.updateEvent.emit(this.module);
        that.close();
      }
    }, 500);
  }

  close() {
    this.closeEvent.emit();
  }

}
