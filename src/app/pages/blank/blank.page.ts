import { Component } from '@angular/core';
import { BlankPageService } from './blank.service';

@Component({
  selector: 'page-auth',
  templateUrl: './blank.page.html',
  styleUrls: ['./blank.page.scss']
})
export class BlankPage {
  constructor(
    private blankPageService: BlankPageService
  ) { }
}
