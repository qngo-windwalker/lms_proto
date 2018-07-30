import { Component, OnInit } from '@angular/core';

import { AppService } from '../app.service';

@Component({
  selector: 'app-panel',
  templateUrl: './panel.component.html'
})
export class PanelComponent implements OnInit {

  constructor(
    public appService: AppService
  ) { }

  ngOnInit() { }
}
