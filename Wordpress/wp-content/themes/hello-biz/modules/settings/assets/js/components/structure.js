import Stack from '@elementor/ui/Stack';
import Typography from '@elementor/ui/Typography';
import { __ } from '@wordpress/i18n';
import { Setting } from './setting';
import { useSettingsContext } from './use-settings-context';
import { Spinner } from '@wordpress/components';

export const Structure = () => {
	const {
		themeSettings: { header_footer: headerFooter, page_title: pageTitle },
		updateSetting,
		isLoading,
	} = useSettingsContext();

	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<Stack gap={ 2 }>
			<Typography
				variant="subtitle2">
				{ __( 'These settings relate to the structure of your pages.', 'hello-biz' ) }
			</Typography>
			<Setting
				value={ headerFooter }
				label={ __( 'Disable theme header and footer', 'hello-biz' ) }
				onSwitchClick={ () => updateSetting( 'header_footer', ! headerFooter ) }
				description={ __( 'What it does: Removes the theme’s default header and footer sections from every page, along with their associated CSS/JS files.', 'hello-biz' ) }
				code={ '<header id="site-header" class="site-header"> ... </header>\n' +
					'<footer id="site-footer" class="site-footer"> ... </footer>' }
				tip={ __( 'Tip: If a Hello+ or Elementor Pro theme builder header or footer already exists on the website, it will get priority. In such case, this setting has no effect and the toggle will be ignored.', 'hello-biz' ) }
			/>
			<Setting
				value={ pageTitle }
				label={ __( 'Hide page title', 'hello-biz' ) }
				onSwitchClick={ () => updateSetting( 'page_title', ! pageTitle ) }
				description={ __( 'What it does: Removes the main page title above your page content.', 'hello-biz' ) }
				code={ '<div class="page-header"><h1 class="entry-title">Post title</h1></div>' }
				tip={ __( 'Tip: If you do not want to display page titles or are using Elementor widgets to display your page titles, hide the page title.', 'hello-biz' ) }
			/>
		</Stack>
	);
};
