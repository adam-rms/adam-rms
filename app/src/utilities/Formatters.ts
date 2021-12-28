/**
 * Convert money strings to locale formatted money string
 * @param currency 3 digit currency string
 * @param amount amount without decimal place
 * @returns formatted string
 */
export const MoneyFormatter = (currency: string, amount: string) => {
  const formatter = new Intl.NumberFormat(undefined, {
    style: "currency",
    currency: currency,
  });
  return formatter.format(parseInt(amount) / 100);
};

/**
 * Format mass to a standard layout
 * @param amount mass
 * @returns formatted string
 */
export const MassFormatter = (amount: number) => {
  const formatter = new Intl.NumberFormat(undefined, {
    style: "decimal",
    minimumSignificantDigits: 2,
    maximumSignificantDigits: 2,
  });
  return formatter.format(amount) + "kg";
};
