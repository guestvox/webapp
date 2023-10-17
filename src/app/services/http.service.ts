import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from "src/environments/environment";
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})

export class HttpService {
  private strBase: string = environment.guestvox.apiUrl

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

  private setHeaders(withoutToken: boolean = false) {
    let headers: {
      "Content-Type": string,
      "Authorization"?: string
    } = {
      "Content-Type": `application/json`
    }

    if (withoutToken === false) {
      headers.Authorization = `Bearer ${localStorage.getItem('authToken')}`
    }

    const requestOptions = {
      headers: new HttpHeaders(headers)
    };

    return requestOptions
  }

  public get(endpoint: string, withoutToken: boolean = false) {
    return new Promise((resolve, reject) => {
      this.fetch.get<any>(this.baseUrl(endpoint), this.setHeaders(withoutToken)).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }

  public post(endpoint: string, body: any, withoutToken: boolean = false) {
    return new Promise((resolve, reject) => {
      this.fetch.post<any>(this.baseUrl(endpoint), body, this.setHeaders(withoutToken)).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }

  public put(endpoint: string, body: any, withoutToken: boolean = false) {
    return new Promise((resolve, reject) => {
      this.fetch.put<any>(this.baseUrl(endpoint), body, this.setHeaders(withoutToken)).subscribe({
        next: (response) => resolve(response),
        error: error => this.fnError(error, reject)
      })
    });
  }
}
