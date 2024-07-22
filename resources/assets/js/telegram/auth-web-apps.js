import queryString from 'query-string';

const parse = queryString.parse(
  queryString.parse(window.location.hash).tgWebAppData,
);

const currentUrl = window.location.href;

const url = new URL(currentUrl);

const params = new URLSearchParams(url.search);

const paramToCheck = 'tgWebAppData';

if (!params.has(paramToCheck)) {
  setTimeout(_ => {
    window.location = route('web-apps::show') + '?' + queryString.stringify(parse);
  }, 1000);
}