let currentRouteName: boolean | string = false;

export function getCurrentRouteName(): string | boolean {
  return currentRouteName;
}

export function setCurrentRouteName(name: string): void {
  if (currentRouteName === false)
    currentRouteName = name;
  else
    console.error(
      'An error occurred while setting the name of the current route. The route name has already been set!',
    );
}
