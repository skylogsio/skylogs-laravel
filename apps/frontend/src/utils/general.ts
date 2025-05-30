export function capitalizeFirstLetter(val: string) {
  return String(val).charAt(0).toUpperCase() + String(val).slice(1);
}

export function truncateLongString(str: string | undefined) {
  if (str && str.length > 100) {
    return `${str.slice(0, 20)} ... ${str.slice(-20)}`;
  }
  return str;
}
