import Stack from '@elementor/ui/Stack';
import Typography from '@elementor/ui/Typography';
import { __ } from '@wordpress/i18n';
import { Setting } from './setting';
import { useSettingsContext } from './use-settings-context';
import { Spinner } from '@wordpress/components';
import Alert from '@elementor/ui/Alert';

export const Theme = () => {
	const {
		themeSettings: { hello_theme: helloTheme },
		updateSetting,
		isLoading,
		themeStyleUrl,
	} = useSettingsContext();

	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<Stack gap={ 2 }>
			<Typography
				variant="subtitle2">
				{ __( 'These settings affect how search engines and assistive technologies interact with your site.', 'hello-biz' ) }
			</Typography>
			<Alert severity="warning" sx={ { mb: 2 } }>
				{ __( 'Be Careful, disabling these settings could break your website.', 'hello-biz' ) }
			</Alert>
			<Setting
				value={ helloTheme }
				label={ __( 'Deregister Hello Biz theme.css', 'hello-biz' ) }
				onSwitchClick={ () => updateSetting( 'hello_theme', ! helloTheme ) }
				description={ __( 'What it does: Turns off CSS reset rules by disabling the theme’s primary stylesheet. CSS reset rules make sure your website looks the same in different browsers.', 'hello-biz' ) }
				code={ `<link rel="stylesheet" href="${ themeStyleUrl }theme.css" />` }
				tip={ __( 'Tip: Deregistering theme.css can make your website load faster. Disable it only if you are not using any WordPress elements on your website, or if you want to style them yourself. Examples of WordPress elements include comments area, pagination box, and image align classes.', 'hello-biz' ) }
			/>
		</Stack>
	);
};
