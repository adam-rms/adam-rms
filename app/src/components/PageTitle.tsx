import { Helmet } from "react-helmet";

type Props = {
	title?: string;
	favicon?: string;
};

/**
 *
 * @param title The page title
 * @param favicon A custom Favicon for the page you're on
 */
const PageTitle = ({ title, favicon }: Props) => {
	return (
		<Helmet>
			<title>
				{title && `${title} - `}AdamRMS
			</title>
			{favicon ? (
				<link rel="shortcut icon" href={favicon} />
			) : (
				<link
					rel="shortcut icon"
					type="image/png"
					href="/assets/icon/favicon.png"
					sizes="32x32"
				/>
			)}
		</Helmet>
	);
};

export default PageTitle;
