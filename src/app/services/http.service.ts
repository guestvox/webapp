import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from "src/environments/environment";
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})

export class HttpService {
  private strBase: string = `${environment.guestvox.apiUrl}/voltux/api/v2`

  constructor(
    private fetch: HttpClient,
    private router: Router
  ) { }

  private baseUrl(endpoint: string): string {
    return this.strBase.concat(endpoint)
  }

  private fnError(error: any, reject: any) {
    if (error.status === 401) {
      this.router.navigate(['login']);
    } else {
      return reject(error)
    }
  }

  private setHeaders() {
    const requestOptions = {
      headers: new HttpHeaders({
        "Authorization": `Bearer ${localStorage.getItem('authToken')}`,
        "Content-Type": `application/json`
      })
    };

    return requestOptions
  }

  public get(endpoint: string, headers = this.setHeaders()) {
    return new Promise((resolve, reject) => {
      this.fetch.get<any>(this.baseUrl(endpoint), headers).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }

  public post(endpoint: string, body: any, headers = this.setHeaders()) {
    return new Promise((resolve, reject) => {
      this.fetch.post<any>(this.baseUrl(endpoint), body, headers).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }

  public put(endpoint: string, body: any, headers = this.setHeaders()) {
    return new Promise((resolve, reject) => {
      this.fetch.put<any>(this.baseUrl(endpoint), body, headers).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }
}
